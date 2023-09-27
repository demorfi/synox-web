<?php declare(strict_types=1);

namespace App\Package;

use App\Components\{Helper, Storage\Journal};
use Digua\LateEvent;
use Digua\Components\{Storage, Storage\SharedMemory};
use Digua\Request\{Query as RequestQuery, FilteredInput};
use Digua\Exceptions\{
    Storage as StorageException,
    Path as PathException
};
use Workerman\Worker as WorkerConnection;
use Exception;

class Worker
{
    /**
     * @var string
     */
    private readonly string $shell;

    /**
     * @var string
     */
    protected readonly string $privateAddress;

    /**
     * @var string
     */
    protected readonly string $publicAddress;

    /**
     * @var array
     */
    private array $buffers = [];

    /**
     * @var array
     */
    private array $linkers = [];

    /**
     * @var WorkerConnection
     */
    protected readonly WorkerConnection $connection;

    /**
     * @throws PathException
     * @throws StorageException
     */
    public function __construct()
    {
        $config = Helper::config('worker');
        LateEvent::subscribe(__CLASS__, fn($message) => Journal::staticPush($message));

        $this->shell          = 'php ' . ROOT_PATH . '/console/worker.php %s';
        $this->privateAddress = $config->get('private');
        $this->publicAddress  = $config->get('public');
        $this->connection     = new WorkerConnection($this->publicAddress);
    }

    /**
     * @param string $format
     * @param mixed  ...$values
     * @return string
     */
    public function notify(string $format, mixed ...$values): string
    {
        $message = sprintf($format, ...$values);
        LateEvent::notify(__CLASS__, $message);
        return $message;
    }

    /**
     * @param string $command
     * @param bool   $async
     * @return bool
     */
    protected function exec(string $command, bool $async = false): bool
    {
        $cmd    = escapeshellcmd(sprintf($this->shell, $command)) . ($async ? ' > /dev/null 2>&1 &' : '');
        $result = exec($cmd, result_code: $code);
        if (!empty($result) || !empty($code)) {
            $this->notify('worker error (%d): %s', $code, $result);
            return false;
        }

        return true;
    }

    /**
     * @param string $hash
     * @param array  $data
     * @return void
     */
    protected function send(string $hash, array $data): void
    {
        $stream = stream_socket_client($this->privateAddress);
        fwrite($stream, json_encode(['hash' => $hash, ...$data]));
        fclose($stream);
    }

    /**
     * @return bool
     */
    public function runParallelService(): bool
    {
        $status = (string)exec(sprintf($this->shell, '--service status'));
        if (str_contains($status, 'Summary')) {
            return true;
        }

        return $this->exec('--service start -d');
    }

    /**
     * @param string  $hash
     * @param Query   $query
     * @param Adapter $package
     * @return bool
     */
    public function runParallelQueue(string $hash, Query $query, Adapter $package): bool
    {
        $queue = base64_encode(serialize([$hash, $query, $package]));
        return $this->exec('--queue ' . escapeshellarg($queue), true);
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function runParallelWatchdog(string $hash): bool
    {
        return $this->exec('--watchdog ' . escapeshellarg($hash), true);
    }

    /**
     * @param string  $hash
     * @param Query   $query
     * @param Adapter $package
     * @return void
     */
    public function queue(string $hash, Query $query, Adapter $package): void
    {
        $type = $package->getItemType()->getName();
        $name = $package->getName();

        $countPayloads = 0;
        try {
            $limitPayloads = Helper::config('app')->get('limitPerPackage');
            $this->notify('search (%s) through the package %s:%s', $query->value, $type, $name);

            foreach ($package->search($query) as $payload) {
                if (!SharedMemory::has($hash)) {
                    $this->notify('search through the package %s:%s interrupted by watchdog?', $type, $name);
                    break;
                }

                if ($countPayloads > $limitPayloads) {
                    $this->notify('search through the package %s:%s interrupted by limit', $type, $name);
                    break;
                }

                if (!empty($payload) && (!$query->hasFilter() || $query->filter->isPasses($payload))) {
                    $this->send($hash, compact('payload'));
                    $countPayloads++;
                }
            }

            $this->notify('found (%d) records through the package %s:%s', $countPayloads, $type, $name);
        } catch (Exception $e) {
            $this->notify($name . ': ' . $e->getMessage());
        } finally {
            $this->send($hash, ['completed' => $package->getId(), 'countPayloads' => $countPayloads]);
        }
    }

    /**
     * @return void
     */
    public function service(): void
    {
        $this->connection->onConnect = function ($connection) {
            $connection->onWebSocketConnect = function ($connection) {
                $hash = (new RequestQuery((new FilteredInput())->refresh(INPUT_SERVER)))->get('hash');

                $this->linkers[$hash] = $connection;

                // Late queue execution
                if (isset($this->buffers[$hash]) && !empty($this->buffers[$hash])) {
                    foreach ($this->buffers[$hash] as $buffer) {
                        // Sending a message to the client
                        $connection->send($buffer);
                    }
                    unset($this->buffers[$hash]);
                }
            };
        };

        $this->connection->onClose = function ($connection) {

            // Freeing up memory
            if (($hash = array_search($connection, $this->linkers)) !== false) {
                unset($this->linkers[$hash]);

                if (isset($this->buffers[$hash])) {
                    unset($this->buffers[$hash]);
                }

                // Stop searching
                if (SharedMemory::has($hash)) {
                    Storage::makeSharedMemory($hash)->free();
                }
            }
        };

        $this->connection->onWorkerStart = function () {
            $connection = new WorkerConnection($this->privateAddress);

            $connection->onMessage = function ($connection, $received) {
                $data = json_decode($received);
                if (!empty($data) && isset($data->hash)) {
                    if (isset($this->linkers[$data->hash])) {
                        // Sending a message to the client
                        $this->linkers[$data->hash]->send($received);
                    } else {

                        // Create a late queue
                        if (!isset($this->buffers[$data->hash])) {
                            $this->buffers[$data->hash] = [];
                        }
                        $this->buffers[$data->hash][] = $received;
                    }
                }
            };

            $connection->listen();
        };

        WorkerConnection::runAll();
    }

    /**
     * @param string $hash
     * @param int    $timeout Seconds
     * @return void
     * @throws StorageException
     */
    public function watchdog(string $hash, int $timeout = 60): void
    {
        $sleep   = 1;
        $storage = Storage::makeSharedMemory($hash);

        while (true) {
            sleep($sleep);
            $timeout -= $sleep;

            $threads = (int)$storage->read();
            if ($threads < 1 || $timeout < 1) {
                $this->notify('search finished' . (($threads >= 1 && $timeout < 1) ? ' by timeout' : ''));
                break;
            }
        }

        $storage->free();
        $this->send($hash, ['finished' => true]);
    }
}