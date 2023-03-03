<?php declare(strict_types=1);

namespace App\Interfaces;

use App\Package\PackageQuery;
use Digua\Components\DataFile;
use Generator;

interface Package
{
    /**
     * @param DataFile $settings Package settings
     */
    public function __construct(DataFile $settings);

    /**
     * Get package name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get short description package.
     *
     * @return string
     */
    public function getShortDescription(): string;

    /**
     * Get package version.
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Has auth of package.
     *
     * @return bool
     */
    public function hasAuth(): bool;

    /**
     * Search.
     *
     * @param PackageQuery $query
     * @return Generator
     */
    public function search(PackageQuery $query): Generator;
}
