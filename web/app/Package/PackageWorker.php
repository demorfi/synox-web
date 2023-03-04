<?php declare(strict_types=1);

namespace App\Package;

use App\Abstracts\PackageItem;
use App\Components\Storage\Journal as JournalStorage;
use Digua\Exceptions\Storage as StorageException;
use Digua\LateEvent;
use Exception;

readonly class PackageWorker
{
    /**
     * @var string
     */
    private string $shell;

    /**
     * @param string $hash
     */
    public function __construct(private string $hash)
    {
        $this->shell = 'php ' . ROOT_PATH . '/console/worker.php %s > /dev/null 2>&1 &';
        LateEvent::subscribe(__CLASS__, fn($message) => JournalStorage::staticPush($message));
    }

    /**
     * @param string         $queueHash
     * @param PackageAdapter $package
     * @param PackageQuery   $query
     * @return false|string
     */
    public function runParallelWorker(string $queueHash, PackageAdapter $package, PackageQuery $query): false|string
    {
        $queue = base64_encode(serialize([$queueHash, $package, $query]));
        return exec(sprintf($this->shell, '--queue ' . $queue));
    }

    /**
     * @return false|string
     */
    public function runParallelWait(): false|string
    {
        return exec(sprintf($this->shell, '--waiting --hash ' . $this->hash));
    }

    /**
     * @param PackageAdapter $package
     * @param PackageQuery   $query
     * @return void
     */
    public function exec(PackageAdapter $package, PackageQuery $query): void
    {
        $type = $package->getType()->getId();
        $name = $package->getName();

        try {
            $stack = new PackageStack($this->hash);

            try {
                LateEvent::notify(
                    __CLASS__,
                    sprintf('%s: search (%s) through the package %s', $type, $query->value, $name)
                );

                foreach ($package->search($query) as $searchResult) {
                    if ($searchResult instanceof PackageItem) {
                        $stack->push($searchResult);
                    }
                }

                LateEvent::notify(
                    __CLASS__,
                    sprintf('%s: found (%d) records through the package %s', $type, $stack->size(), $name)
                );
            } catch (Exception $e) {
                LateEvent::notify(__CLASS__, $name . ': ' . $e->getMessage());
            } finally {
                $stack->setEof();
            }
        } catch (StorageException $e) {
            LateEvent::notify(__CLASS__, $name . ': ' . $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function wait(): void
    {
        try {
            $stack = new PackageStack($this->hash);
            $query = (array)$stack->shadow()->current();

            if (!empty($query) && isset($query['queues'])) {
                while (sizeof($query['queues'])) {
                    foreach ($query['queues'] as $key => $queueHash) {
                        $workerStack = new PackageStack($queueHash);
                        if ($workerStack->hasEof()) {
                            unset($query['queues'][$key]);
                        }
                    }
                    usleep(100000);
                }

                $stack->setEof();
                LateEvent::notify(
                    __CLASS__,
                    sprintf('%s: search(%s) finished', $query['type'], $query['value'])
                );
            }
        } catch (StorageException $e) {
            LateEvent::notify(__CLASS__, $e->getMessage());
        }
    }
}