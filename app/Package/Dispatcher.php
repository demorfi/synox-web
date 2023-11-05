<?php declare(strict_types=1);

namespace App\Package;

use App\Collections\Package as Collection;
use App\Components\Helper;
use App\Enums\ItemType;
use App\Interfaces\PackageContent;
use App\Exceptions\PackageDispatcher as PackageDispatcherException;
use App\Repositories\Packages as Repository;
use Digua\LateEvent;
use Exception;

final class Dispatcher
{
    /**
     * @var ?Collection
     */
    private ?Collection $packages = null;

    /**
     * @return string
     */
    protected function makeHash(): string
    {
        return (string)Helper::makeIntHash();
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
     * @return string Query hash
     * @throws PackageDispatcherException
     * @uses LateEvent::notify
     */
    public function makeNewSearchQuery(string $query, ?Filter $filter = null): string
    {
        if (empty($query)) {
            throw new PackageDispatcherException('Missing search query!');
        }

        if (!$this->usesPackages()) {
            throw new PackageDispatcherException('No packages available to search!');
        }

        $worker = new Worker();
        if (!$worker->runParallelService()) {
            throw new PackageDispatcherException('Failed running parallel service!');
        }

        $hash  = $this->makeHash();
        $query = new Query($query, $filter);

        sleep(1);
        $worker->addQueue($hash, $query, $this->packages);
        LateEvent::notify(__CLASS__, sprintf('search query (%s) created', $query->value));
        return $hash;
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