<?php declare(strict_types=1);

namespace App\Package\Search;

use App\Package\{Adapter, Repository, Collection as PackageCollection};
use App\Package\Search\Exceptions\WorkerQueue as WorkerQueueException;
use App\Components\{Helper, Storage\Journal};
use Digua\Components\{Storage, Storage\DiskFile, Storage\SharedMemory};
use Digua\Exceptions\{Path as PathException, Storage as StorageException};
use Digua\LateEvent;
use Digua\Request\{FilteredInput, Query as RequestQuery};
use Workerman\{Connection\ConnectionInterface as WorkerConnectionInterface, Worker as WorkerConnection};
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
    private array $queues = [];

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

        // Wakeup events
        Repository::getInstance()->getPackages()->getByEnabled()->each(function (Adapter $package) {
            $package->wakeup();
        });

        WorkerConnection::$statusFile = DiskFile::getDiskPath('worker-' . posix_getpid() . '.status');
        WorkerConnection::$pidFile    = DiskFile::getDiskPath('worker.pid');
        WorkerConnection::$logFile    = DiskFile::getDiskPath('worker.log');

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
            $this->notify('Worker error (%d): %s', $code, $result);
            return false;
        }

        return true;
    }

    /**
     * @param string            $hash
     * @param Query             $query
     * @param PackageCollection $packages
     * @return void
     */
    public function addQueue(string $hash, Query $query, PackageCollection $packages): void
    {
        $queue = base64_encode(serialize(compact('query', 'packages')));
        $this->send($hash, compact('queue'));
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
        if (!str_contains($status, 'not run')) {
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
        $type = $package->instance()->getType()->getName();
        $name = $package->getName();

        $countPayloads = 0;
        try {
            $limitPayloads = Helper::config('app')->get('limitPerPackage');
            $this->notify('Search (%s) through the package %s:%s', $query->value, $type, $name);

            foreach ($package->search($query) as $payload) {
                if (!SharedMemory::has($hash)) {
                    $this->notify('Search through the package %s:%s interrupted by watchdog?', $type, $name);
                    break;
                }

                if ($countPayloads > $limitPayloads) {
                    $this->notify('Search through the package %s:%s interrupted by limit', $type, $name);
                    break;
                }

                if (!empty($payload) && (!$query->hasFilter() || $query->filter->isPasses($payload))) {
                    $this->send($hash, compact('payload'));
                    $countPayloads++;
                }
            }

            $this->notify('Found (%d) records through the package %s:%s', $countPayloads, $type, $name);
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
        if (WorkerConnection::getStatus() === WorkerConnection::STATUS_RUNNING) {
            return;
        }

        $this->connection->onConnect = function ($connection) {
            $connection->onWebSocketConnect = $this->socketConnect(...);
        };

        $this->connection->onClose = $this->socketClose(...);

        $this->connection->onWorkerStart = function () {
            $connection = new WorkerConnection($this->privateAddress);

            $connection->onMessage = $this->socketMessage(...);
            $connection->listen();
        };

        WorkerConnection::runAll();
    }

    /**
     * @param WorkerConnectionInterface $connection
     * @return void
     */
    private function socketConnect(WorkerConnectionInterface $connection): void
    {
        try {
            $hash = (new RequestQuery((new FilteredInput())->refresh(INPUT_SERVER)))->get('hash');
            if (empty($hash)) {
                throw new WorkerQueueException('Required hash not passed!');
            }

            $this->linkers[$hash] = $connection;
            if (!isset($this->queues[$hash]) || empty($this->queues[$hash])) {
                throw new WorkerQueueException('No search queue created!');
            }

            $queue = (array)unserialize((string)base64_decode($this->queues[$hash], true));
            if (!isset($queue['query'], $queue['packages'])
                || !($queue['query'] instanceof Query)
                || !($queue['packages'] instanceof PackageCollection)) {
                unset($this->queues[$hash]);
                throw new WorkerQueueException('Search queue is broken!');
            }

            $storage = Storage::makeSharedMemory($hash);
            $threads = $queue['packages']->count();
            $storage->write((string)$threads);

            if (!$this->runParallelWatchdog($hash)) {
                $storage->free();
                throw new WorkerQueueException('Failed running parallel watchdog!');
            }

            $this->notify('Search (%s) running', $queue['query']->value);
            foreach ($queue['packages'] as $package) {
                /* @var $package Adapter */
                if (!$this->runParallelQueue($hash, $queue['query'], $package)) {
                    $storage->rewrite((string)(--$threads));
                }
            }

            $this->notify('Running (%d) threads', $threads);
            $this->send($hash, compact('threads'));
        } catch (Exception $e) {
            $connection->send(['error' => $e->getMessage(), 'finished' => true]);
        }
    }

    /**
     * @param WorkerConnectionInterface $connection
     * @return void
     * @throws StorageException
     */
    private function socketClose(WorkerConnectionInterface $connection): void
    {
        // Freeing up memory
        if (($hash = array_search($connection, $this->linkers)) !== false) {
            unset($this->linkers[$hash]);

            if (isset($this->queues[$hash])) {
                unset($this->queues[$hash]);
            }

            // Stop searching
            if (SharedMemory::has($hash)) {
                Storage::makeSharedMemory($hash)->free();
            }
        }
    }

    /**
     * @param WorkerConnectionInterface $connection
     * @param string                    $received
     * @return void
     */
    private function socketMessage(WorkerConnectionInterface $connection, string $received): void
    {
        $data = json_decode($received);
        if (!isset($data->hash)) {
            return;
        }

        // Add new search queue
        if (isset($data->queue)) {
            $this->queues[$data->hash] = $data->queue;
            return;
        }

        // Sending a message to the client
        if (isset($this->linkers[$data->hash])) {
            $this->linkers[$data->hash]->send($received);
        }
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
                $this->notify('Search finished' . (($threads >= 1 && $timeout < 1) ? ' by timeout' : ''));
                break;
            }
        }

        $storage->free();
        $this->send($hash, ['finished' => true]);
    }
}