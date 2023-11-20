<?php declare(strict_types=1);

namespace App\Package\Search\Interfaces;

use App\Package\Interfaces\Package as PackageInterface;
use App\Package\Search\{Enums\Type, Query};
use Generator;

interface Package extends PackageInterface
{
    /**
     * @return Type
     */
    public function getType(): Type;

    /**
     * @return bool
     */
    public function hasAuth(): bool;

    /**
     * @param Query $query
     * @return ?Generator
     */
    public function search(Query $query): ?Generator;

    /**
     * @param string $id
     * @return ?Content
     */
    public function fetch(string $id): ?Content;
}