<?php declare(strict_types=1);

namespace App\Package\Search\Interfaces;

use App\Package\Interfaces\Package as PackageInterface;
use App\Package\Search\{Enums\Subtype, Query};

interface Package extends PackageInterface
{
    /**
     * @return Subtype
     */
    public function getSubtype(): Subtype;

    /**
     * @return bool
     */
    public function hasAuth(): bool;

    /**
     * @param Query $query
     * @return ?iterable
     */
    public function search(Query $query): ?iterable;

    /**
     * @param Query $query
     * @return ?Content
     */
    public function fetch(Query $query): ?Content;
}