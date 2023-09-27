<?php declare(strict_types=1);

namespace App\Package;

use App\Collections\Package as Collection;
use App\Components\Helper;
use App\Enums\ItemType;
use App\Interfaces\PackageContent;
use App\Exceptions\PackageDispatcher as PackageDispatcherException;
use App\Repositories\Packages as Repository;
use Digua\LateEvent;
use Digua\Components\Storage;
use Digua\Exceptions\{
    MemoryShared as MemorySharedException,
    Storage as StorageException
};
use Exception;

final class Dispatcher
{
    /**
     * @var ?Collection
     */
    private ?Collection $packages = null;

    /**
     * @var ?Query
     */
    private ?Query $query = null;

    /**
     * @param ?string $hash
     */
    public function __construct(private ?string $hash = null)
    {
    }

    /**
     * @return string
     */
    public function makePackageHash(): string
    {
        return $this->hash = (string)Helper::makeIntHash();
    }

    /**
     * @return bool
     */
    public function hasHash(): bool
    {
        return !empty($this->hash);
    }

    /**
     * @param ?ItemType $type
     * @param array     $onlyPackages
     * @return bool
     */
    public function usePackages(?ItemType $type = null, array $onlyPackages = []): bool
    {
        $this->packages = Repository::getInstance()->getPackages()
            ->getByEnabled()
            ->filter(fn($item) => (empty($type) || $item->getType() === $type)
                && (empty($onlyPackages) || in_array($item->getId(), $onlyPackages))
            );

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
     * @param string  $query
     * @param ?Filter $filter
     * @return Query
     */
    public function makeNewSearchQuery(string $query, ?Filter $filter = null): Query
    {
        return $this->query = new Query($query, $filter);
    }

    /**
     * @return int
     * @throws PackageDispatcherException
     * @throws StorageException
     * @throws MemorySharedException
     * @uses LateEvent::notify
     */
    public function search(): int
    {
        if (is_null($this->query)) {
            throw new PackageDispatcherException('Missing search query!');
        }

        if (!$this->hasHash()) {
            throw new PackageDispatcherException('Required hash not passed!');
        }

        if (!$this->usesPackages()) {
            throw new PackageDispatcherException('No packages available to search!');
        }

        $worker = new Worker();
        if (!$worker->runParallelService()) {
            throw new PackageDispatcherException('Failed running parallel service!');
        }

        $storage = Storage::makeSharedMemory($this->hash);
        $threads = $this->packages->count();
        $storage->write((string)$threads);

        if (!$worker->runParallelWatchdog($this->hash)) {
            $storage->free();
            throw new PackageDispatcherException('Failed running parallel watchdog!');
        }

        LateEvent::notify(__CLASS__, sprintf('search (%s) running', $this->query->value));
        foreach ($this->packages as $package) {
            /* @var $package Adapter */
            if (!$worker->runParallelQueue($this->hash, $this->query, $package)) {
                $storage->rewrite((string)(--$threads));
            }
        }

        LateEvent::notify(__CLASS__, sprintf('running (%d) threads', $threads));
        return $threads;
    }

    /**
     * @param string $packageId
     * @param string $fetchId
     * @return PackageContent
     * @throws PackageDispatcherException
     * @uses LateEvent::notify
     */
    public function fetch(string $packageId, string $fetchId): PackageContent
    {
        if (!$this->usesPackages()) {
            throw new PackageDispatcherException('No packages available to fetching!');
        }

        $package = $this->packages->find($packageId);
        if (!$package) {
            throw new PackageDispatcherException('The requested package was not found!');
        }

        $content = $package->getContentType()->make();

        try {
            $package->fetch($fetchId, $content);
        } catch (Exception $e) {
            LateEvent::notify(__CLASS__, $package->getName() . ': ' . $e->getMessage());
        }

        $type   = $package->getContentType()->getId();
        $access = $content->isAvailable() ? 'available' : 'unable';
        LateEvent::notify(
            __CLASS__,
            sprintf('%s: fetch %s (%s) through the package %s', $type, $access, $fetchId, $package->getName())
        );

        return $content;
    }
}