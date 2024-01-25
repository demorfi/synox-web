<?php declare(strict_types=1);

namespace App\Package\Search;

use App\Package\{Collection, Enums\Type as PackageType, Repository};
use App\Package\Exceptions\Dispatcher as PackageDispatcherException;
use App\Package\Search\Interfaces\Content;
use App\Components\Helper;
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
    protected function makeToken(): string
    {
        return md5((string)Helper::makeIntHash());
    }

    /**
     * @param ?array  $onlyPackages
     * @param ?Filter $filter
     * @return bool
     */
    private function usePackages(array $onlyPackages = null, ?Filter $filter = null): bool
    {
        $onlyPackages   ??= $filter?->collection()->get('packages') ?: [];
        $extraFilters   = $filter?->collection()->except('packages')->toArray() ?: [];
        $this->packages = Repository::getInstance()->getPackages()
            ->getByType(PackageType::SEARCH)
            ->getByEnabled()
            ->filterByType(static function ($item) use ($onlyPackages, $extraFilters, $filter): bool {
                return (empty($onlyPackages) || in_array($item->getId(), $onlyPackages))
                    && (empty($extraFilters) || $filter->isPasses($item->instance()));
            });

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
     * @param array   $params
     * @return string Query token
     * @throws PackageDispatcherException
     * @uses LateEvent::notify
     */
    public function makeNewSearchQuery(string $query, ?Filter $filter = null, array $params = []): string
    {
        if (empty($query)) {
            throw new PackageDispatcherException('Missing search query!');
        }

        $this->usePackages(filter: $filter);
        if (!$this->usesPackages()) {
            throw new PackageDispatcherException('No packages available to search!');
        }

        $worker = new Worker();
        if (!$worker->runParallelService()) {
            throw new PackageDispatcherException('Failed running parallel service!');
        }

        $token = $this->makeToken();
        $query = new Query($query, $filter, $params);

        usleep(500000);
        $worker->addQueue($token, $query, $this->packages);
        LateEvent::notify(__CLASS__, sprintf('Search query (%s) created', $query->value));
        return $token;
    }

    /**
     * @param string $packageId
     * @param string $fetchId
     * @return ?Content
     * @throws PackageDispatcherException
     * @uses LateEvent::notify
     */
    public function fetch(string $packageId, string $fetchId): ?Content
    {
        if (!$this->usePackages([$packageId])) {
            throw new PackageDispatcherException('The requested package was not found!');
        }

        $content = null;
        $package = $this->packages->find($packageId);

        try {
            $content = $package->fetch($fetchId);
        } catch (Exception $e) {
            LateEvent::notify(__CLASS__, $package->getName() . ': ' . $e->getMessage());
        }

        $type   = $package->instance()->getType()->getName();
        $access = $content?->isAvailable() ? 'available' : 'unable';

        LateEvent::notify(
            __CLASS__,
            sprintf('%s: fetch %s (%s) through the package %s', $type, $access, $fetchId, $package->getName())
        );

        return $content;
    }
}