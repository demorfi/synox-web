<?php declare(strict_types=1);

namespace App\Interfaces;

use App\Enums\{ContentType, ItemType};
use App\Package\Query;
use Digua\Components\DataFile;
use Generator;

interface Package
{
    /**
     * @param DataFile $settings Package settings
     */
    public function __construct(DataFile $settings);

    /**
     * @return ItemType
     */
    public function getItemType(): ItemType;

    /**
     * @return ContentType
     */
    public function getContentType(): ContentType;

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
     * @param Query $query
     * @return Generator
     */
    public function search(Query $query): Generator;

    /**
     * @param string         $id
     * @param PackageContent $content
     * @return bool
     */
    public function fetch(string $id, PackageContent $content): bool;
}
