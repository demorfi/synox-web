<?php declare(strict_types=1);

namespace App\Package\Extension\Abstracts;

use App\Package\Abstracts\Package as BasePackage;
use App\Package\Extension\Interfaces\Package as ExtensionPackageInterface;

abstract class Package extends BasePackage implements ExtensionPackageInterface
{
}