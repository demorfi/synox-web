<?php declare(strict_types=1);

namespace App\Package\Download;

use App\Abstracts\PackageFilter as PackageFilterAbstract;
use App\Enums\Category;

class Filter extends PackageFilterAbstract
{
    /**
     * @inheritdoc
     */
    protected static array $uses = [Category::class];
}