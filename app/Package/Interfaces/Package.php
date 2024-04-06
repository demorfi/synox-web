<?php declare(strict_types=1);

namespace App\Package\Interfaces;

use App\Package\Settings;

interface Package
{
    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return string
     */
    public function getVersion(): string;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @return string[]
     */
    public function getRequires(): array;

    /**
     * @return void
     */
    public function wakeup(): void;
}