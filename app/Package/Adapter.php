<?php declare(strict_types=1);

namespace App\Package;

use App\Interfaces\Package;
use Digua\Components\DataFile;
use Digua\Exceptions\Storage as StorageException;
use Generator;
use JsonSerializable;
use BadMethodCallException;

/**
 * @mixin Package
 */
final readonly class Adapter implements JsonSerializable
{
    /**
     * @param Package  $package  Package instance
     * @param DataFile $settings Package settings
     * @throws StorageException
     */
    public function __construct(
        private Package $package,
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
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->package->getName(),
            'type'        => $this->package->getItemType()->getName(),
            'version'     => $this->package->getVersion(),
            'description' => $this->package->getShortDescription(),
            'usesAuth'    => $this->package->hasAuth(),
            'enabled'     => $this->isEnabled(),
            'settings'    => $this->settings->collection()
                ->except('enabled')
                ->replaceValue('password', fn($v) => !empty($v) ? 'password' : '')
        ];
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

    /**
     * @param Query $query
     * @return Generator
     */
    public function search(Query $query): Generator
    {
        foreach ($this->package->search($query) as $result) {
            yield is_a($result, $this->package->getItemType()->getInterface()) ? $result : null;
        }
    }
}
