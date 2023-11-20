<?php declare(strict_types=1);

namespace App\Package\Interfaces;

use App\Components\Settings;

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
     * @return void
     */
    public function wakeup(): void;
}