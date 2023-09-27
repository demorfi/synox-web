<?php declare(strict_types=1);

namespace App\Abstracts;

use App\Interfaces\FilterEnum;
use Digua\Components\ArrayCollection;
use Generator;

class PackageFilter
{
    /**
     * @var string[]
     */
    protected static array $uses = [];

    /**
     * @param array $filters
     */
    public function __construct(protected readonly array $filters)
    {
    }

    /**
     * @return ?Generator
     */
    public static function uses(): ?Generator
    {
        foreach (static::$uses as $enum) {
            if (is_subclass_of($enum, FilterEnum::class)) {
                yield $enum::getId() => $enum;
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
                'name'  => $enum::getName(),
                'cases' => $enum::cases()
            ];
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

        if (!empty($filters)) {
            /* @var FilterEnum $enum */
            $enum = iterator_to_array(self::uses())[$id] ?? null;
            if (!empty($enum)) {
                foreach ($filters as $filter) {
                    if (($case = $enum::tryFrom($filter)) !== null) {
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
        foreach (array_keys($this->filters) as $filter) {
            $this->getById($filter)->each($callable);
        }
    }
}