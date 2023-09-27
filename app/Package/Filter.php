<?php declare(strict_types=1);

namespace App\Package;

use App\Interfaces\FilterEnum;
use App\Abstracts\{PackageFilter, PackageItem};
use App\Enums\Category;
use Fiber;
use Throwable;

class Filter extends PackageFilter
{
    /**
     * @inheritdoc
     */
    protected static array $uses = [Category::class];

    /**
     * @param PackageItem $item
     * @return bool
     */
    public function isPasses(PackageItem $item): bool
    {
        if (!$this->count()) {
            return true;
        }

        $fiber = new Fiber(function (PackageItem $item): void {
            $this->each(function (FilterEnum $case) use ($item): void {
                if ($case->value === $item->{$case::getId()}) {
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