<?php declare(strict_types=1);

namespace App\Package\Search\Interfaces;

use App\Package\Interfaces\BaseEnum;

interface FilterEnum extends BaseEnum
{
    /**
     * @return string
     */
    public static function getFilterName(): string;

    /**
     * @return string
     */
    public static function getFilterId(): string;
}