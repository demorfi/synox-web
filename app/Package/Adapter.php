<?php declare(strict_types=1);

namespace App\Package;

use App\Components\Settings;
use App\Package\Abstracts\Relay;
use Digua\Exceptions\Storage as StorageException;
use JsonSerializable;

/**
 * @mixin Relay
 */
final readonly class Adapter implements JsonSerializable
{
    /**
     * @param Relay    $relay
     * @param Settings $settings
     */
    public function __construct(private Relay $relay, private Settings $settings)
    {
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->relay->getId(),
            'type'     => $this->relay->getType()->getName(),
            'enabled'  => $this->isEnabled(),
            'settings' => $this->settings->collection()
                ->except('enabled')
                ->replaceValue('password', fn($v) => !empty($v) ? 'password' : ''),
            ...$this->relay->jsonSerialize(),
        ];
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->relay->$name(...$arguments);
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
     * @return Settings
     */
    public function getSettings(): Settings
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