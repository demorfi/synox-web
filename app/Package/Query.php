<?php declare(strict_types=1);

namespace App\Package;

readonly class Query
{
    /**
     * @param string  $value
     * @param ?Filter $filter
     */
    public function __construct(
        public string $value,
        public ?Filter $filter = null
    ) {
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->filter instanceof Filter;
    }
}