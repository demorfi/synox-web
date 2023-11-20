<?php declare(strict_types=1);

namespace App\Package\Interfaces;

use BackedEnum;

interface BaseEnum extends BackedEnum
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $name
     * @return ?self
     */
    public static function tryName(string $name): ?self;
}