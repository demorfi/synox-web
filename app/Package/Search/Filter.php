<?php declare(strict_types=1);

namespace App\Package\Search;

use App\Package\Search\{Abstracts\Item, Abstracts\Package, Enums\Category, Interfaces\FilterEnum};
use Digua\Components\ArrayCollection;
use JsonSerializable;
use Fiber;
use Throwable;

class Filter implements JsonSerializable
{
    /**
     * @var array|string[]
     */
    protected static array $uses = [Category::class];

    /**
     * @param array $filters
     */
    public function __construct(protected readonly array $filters)
    {
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->filters;
    }

    /**
     * @return ?iterable
     */
    final public static function uses(): ?iterable
    {
        foreach (static::$uses as $enum) {
            if (is_subclass_of($enum, FilterEnum::class)) {
                yield $enum::getFilterId() => $enum;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection
     */
    public static function usesCollection(): ArrayCollection
    {
        $collection = ArrayCollection::make();
        foreach (self::uses() as $id => $enum) {
            $collection[] = [
                'id'    => $id,
                'name'  => $enum::getFilterName(),
                'cases' => $enum::cases()
            ];
        }

        return $collection;
    }

    /**
     * @return ArrayCollection
     */
    public static function usesCasesCollection(): ArrayCollection
    {
        $collection = ArrayCollection::make();
        foreach (self::uses() as $id => $enum) {
            $collection[$id] = $enum::cases();
        }

        return $collection;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return sizeof($this->filters);
    }

    /**
     * @param string $id
     * @return ArrayCollection<FilterEnum>
     */
    public function getById(string $id): ArrayCollection
    {
        $collection = ArrayCollection::make();
        $filters    = $this->filters[$id] ?? [];
        if (is_array($filters) && sizeof($filters) >= 1) {
            /* @var FilterEnum $enum */
            $enum = iterator_to_array(self::uses())[$id] ?? null;
            if (!empty($enum)) {
                $filters = array_unique($filters, SORT_REGULAR);
                foreach ($filters as $filter) {
                    if (($case = $enum::tryFrom($filter instanceof FilterEnum ? $filter->value : $filter)) !== null) {
                        $collection[] = $case;
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * @return ArrayCollection
     */
    public function collection(): ArrayCollection
    {
        return ArrayCollection::make($this->filters);
    }

    /**
     * @param callable $callable
     * @return void
     */
    public function each(callable $callable): void
    {
        foreach (array_keys($this->filters) as $id) {
            $this->getById((string)$id)->each($callable);
        }
    }

    /**
     * @param Item|Package $item
     * @return bool
     */
    public function isPasses(Item|Package $item): bool
    {
        if (!$this->count()) {
            return true;
        }

        $fiber = new Fiber(function (Item|Package $item): void {
            $this->each(static function (FilterEnum $case) use ($item): void {
                $caseId = $case::getFilterId();
                if (($item instanceof Item && $case->value === $item->{$caseId})
                    || ($item instanceof Package && !$item->onlyAllowed()->getById($caseId)->search($case)->isEmpty())) {
                    Fiber::suspend(true);
                }
            });
        });

        try {
            return $fiber->start($item) === true;
        } catch (Throwable) {
            return false;
        }
    }
}