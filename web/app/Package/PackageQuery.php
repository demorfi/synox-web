<?php declare(strict_types=1);

namespace App\Package;

use App\Abstracts\PackageFilter;

readonly class PackageQuery
{
    /**
     * @param string              $value
     * @param ?PackageFilter|null $filter
     */
    public function __construct(
        public string $value,
        public ?PackageFilter $filter = null
    ) {
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->filter instanceof PackageFilter && $this->filter->count();
    }
}