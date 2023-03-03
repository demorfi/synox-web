<?php declare(strict_types=1);

namespace App\Abstracts;

use App\Interfaces\FilterEnum as FilterEnumInterface;

class PackageFilter
{
    /**
     * @var string[]
     */
    protected static array $uses = [];

    /**
     * @param array $filters
     */
    public function __construct(private readonly array $filters)
    {
    }

    /**
     * @param string $id
     * @return ?FilterEnumInterface
     */
    public function getById(string $id): ?FilterEnumInterface
    {
        if (isset($this->filters[$id])) {
            foreach (static::$uses as $filter) {
                if (is_subclass_of($filter, FilterEnumInterface::class) && $filter::getId() === $id) {
                    foreach ($filter::cases() as $case) {
                        if ($case->name === $this->filters[$id]) {
                            return $case;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        $list = [];
        foreach (array_keys($this->filters) as $filter) {
            if (($case = $this->getById($filter)) !== null) {
                $list[] = $case;
            }
        }

        return $list;
    }

    /**
     * @param callable $callable
     * @return void
     */
    public function each(callable $callable): void
    {
        foreach ($this->getList() as $case) {
            $callable($case);
        }
    }

    /**
     * @return array
     */
    public static function uses(): array
    {
        return static::$uses;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return sizeof($this->filters);
    }

    /**
     * @return array
     */
    protected function toArray(): array
    {
        return $this->filters;
    }
}