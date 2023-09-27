<?php declare(strict_types=1);

namespace App\Interfaces;

use BackedEnum;

interface FilterEnum extends BackedEnum
{
    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @return string
     */
    public static function getId(): string;
}
