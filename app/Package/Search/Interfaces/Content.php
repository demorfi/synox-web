<?php declare(strict_types=1);

namespace App\Package\Search\Interfaces;

use App\Package\Search\Enums\Type;
use JsonSerializable;

interface Content extends JsonSerializable
{
    /**
     * @param string $content
     * @return void
     */
    public function add(string $content): void;

    /**
     * @param string $content
     * @return bool
     */
    public function is(string $content): bool;

    /**
     * @return string
     */
    public function get(): string;

    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @return Type
     */
    public function getType(): Type;
}