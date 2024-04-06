<?php declare(strict_types=1);

namespace App\Package\Extension\Interfaces;

use App\Package\Interfaces\Package as PackageInterface;
use App\Package\Extension\Enums\Subtype;

interface Package extends PackageInterface
{
    /**
     * @return Subtype
     */
    public function getSubtype(): Subtype;
}