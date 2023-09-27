<?php declare(strict_types=1);

namespace App\Interfaces;

use JsonSerializable;
use App\Enums\ContentType;

interface PackageContent extends JsonSerializable
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
     * @return ContentType
     */
    public function getType(): ContentType;
}