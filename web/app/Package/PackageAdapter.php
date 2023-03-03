<?php declare(strict_types=1);

namespace App\Package;

use App\Interfaces\{
    Package as PackageInterface,
    Download as DownloadInterface,
    Lyrics as LyricsInterface
};
use App\Enums\PackageType;
use Digua\Components\DataFile;
use Digua\Exceptions\Storage as StorageException;
use BadMethodCallException;

/**
 * @mixin PackageInterface
 * @mixin DownloadInterface
 * @mixin LyricsInterface
 */
final readonly class PackageAdapter
{
    /**
     * @param PackageType      $type
     * @param PackageInterface $package  Package instance
     * @param DataFile         $settings Package settings
     * @throws StorageException
     */
    public function __construct(
        private PackageType $type,
        private PackageInterface $package,
        private DataFile $settings
    ) {
        $this->settings->read();
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->package, $name)) {
            throw new BadMethodCallException('Method ' . $name . ' does not exist!');
        }

        return $this->package->$name(...$arguments);
    }

    /**
     * Get package setting.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->settings->get($name);
    }

    /**
     * Set package setting.
     *
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->settings->set($name, $value);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        $className = get_class($this->package);
        return substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * @return PackageType
     */
    public function getType(): PackageType
    {
        return $this->type;
    }

    /**
     * @return DataFile
     */
    public function getSettings(): DataFile
    {
        return $this->settings;
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function saveSettings(): void
    {
        $this->settings->save();
    }

    /**
     * Is enabled package.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->settings->get('enabled', false);
    }
}
