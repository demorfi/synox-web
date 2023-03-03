<?php declare(strict_types=1);

namespace App\Package;

use App\Package\{
    Download\Torrent as TorrentDownload,
    Lyrics\Content as ContentLyrics
};
use App\Repositories\Packages as PackagesRepository;
use App\Abstracts\{PackageItem, PackageFilter};
use App\Collections\Package as PackageCollection;
use App\Exceptions\PackageDispatcher as PackageDispatcherException;
use App\Enums\PackageType;
use Digua\LateEvent;
use Digua\Exceptions\Storage as StorageException;
use Exception;

final class PackageDispatcher
{
    /**
     * @var ?PackageCollection
     */
    private ?PackageCollection $packages = null;

    /**
     * @var ?PackageQuery
     */
    private ?PackageQuery $query = null;

    /**
     * @param ?string $hash
     */
    public function __construct(private ?string $hash = null)
    {
    }

    /**
     * @return bool
     */
    public function hasHash(): bool
    {
        return !empty($this->hash);
    }

    /**
     * @return string
     */
    public function makePackageHash(): string
    {
        return $this->hash = PackageStack::makeHash();
    }

    /**
     * @param PackageType $type
     * @param array       $onlyPackages
     * @return bool
     */
    public function usePackages(PackageType $type, array $onlyPackages = []): bool
    {
        $this->packages = PackagesRepository::getInstance()->getPackages()
            ->getByType($type)->getByEnabled()
            ->filter(fn($item) => empty($onlyPackage) || in_array($item->getId(), $onlyPackages));

        return (bool)$this->packages->count();
    }

    /**
     * @return int
     */
    public function usesPackages(): int
    {
        return $this->packages?->count() ?: 0;
    }

    /**
     * @param string         $query
     * @param ?PackageFilter $filter
     * @return PackageQuery
     */
    public function makeNewSearchQuery(string $query, ?PackageFilter $filter = null): PackageQuery
    {
        return $this->query = new PackageQuery($query, $filter);
    }

    /**
     * @return bool
     * @throws StorageException
     * @throws PackageDispatcherException
     * @uses LateEvent::notify
     */
    public function search(): bool
    {
        if (!$this->usesPackages()) {
            throw new PackageDispatcherException('No packages available to search!');
        }

        if (is_null($this->query)) {
            throw new PackageDispatcherException('Missing search term!');
        }

        $type = $this->packages->type->getId();
        LateEvent::notify(__CLASS__, sprintf('%s: search (%s) running', $type, $this->query->value));

        $queues = [];
        $stack  = new PackageStack($this->hash ?: $this->makePackageHash());
        $worker = new PackageWorker($stack->getHash());

        foreach ($this->packages as $package) {
            /* @var $package PackageAdapter */
            $queueHash = $stack::makeHash();
            $queues[]  = $queueHash;
            $worker->runParallelWorker($queueHash, $package, $this->query);
        }

        $stack->push([
            'type'   => $type,
            'value'  => $this->query->value,
            'queues' => $queues
        ]);

        $worker->runParallelWait();
        return true;
    }

    /**
     * @param array $chunks
     * @param int   $limit
     * @return int
     * @throws StorageException
     * @throws PackageDispatcherException
     */
    public function result(array &$chunks, int $limit = 0): int
    {
        if (!$this->hasHash()) {
            throw new PackageDispatcherException('Stack hash not found!');
        }

        $stack = new PackageStack($this->hash);
        $query = (array)$stack->shadow()->current();
        if (empty($query) || !isset($query['queues'])) {
            $stack->free();
            return -1;
        }

        while (sizeof($query['queues'])) {
            $emptyIteration = 0;
            foreach ($query['queues'] as $key => $queueHash) {
                $workerStack = new PackageStack($queueHash);
                if (!$workerStack->size()) {
                    $emptyIteration++;

                    // Remove an empty queue from the list of tasks
                    if ($workerStack->hasEof()) {
                        $workerStack->free();
                        unset($query['queues'][$key]);
                        $stack->pull();
                        $stack->push($query);
                    }
                } else {

                    // Populate the list sequentially. Take only the first two information of the queue
                    for ($i = 0; ($i < 2 && ($limit <= 0 || $limit > sizeof($chunks))); $i++) {
                        $data = $workerStack->shift();
                        if ($data instanceof PackageItem) {
                            $chunks[] = $data;
                        }
                    }

                    if ($limit > 0 && sizeof($chunks) >= $limit) {
                        return sizeof($chunks);
                    }
                }

                if ($emptyIteration >= sizeof($query['queues'])) {
                    return sizeof($chunks);
                }
            }
        }

        $stack->free();
        return sizeof($chunks);
    }

    /**
     * @param string $packageId
     * @param string $url
     * @return TorrentDownload|ContentLyrics Depending on the package type
     * @throws PackageDispatcherException
     * @uses LateEvent::notify
     */
    public function fetch(string $packageId, string $url): TorrentDownload|ContentLyrics
    {
        if (!$this->usesPackages()) {
            throw new PackageDispatcherException('No packages available to fetching!');
        }

        $package = $this->packages->find($packageId);
        if (!$package) {
            throw new PackageDispatcherException('The requested package was not found!');
        }

        $data = match ($packageType = $package->getType()) {
            PackageType::Download => new TorrentDownload,
            PackageType::Lyrics => new ContentLyrics
        };

        try {
            $package->fetch($url, $data);
        } catch (Exception $e) {
            LateEvent::notify(__CLASS__, $package->getName() . ': ' . $e->getMessage());
        }

        $type   = $packageType->getId();
        $access = $data->isAvailable() ? 'available' : 'unable';
        LateEvent::notify(
            __CLASS__,
            sprintf('%s: fetch %s (%s) through the package %s', $type, $access, $url, $package->getName())
        );

        return $data;
    }
}