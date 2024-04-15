<?php declare(strict_types=1);

namespace App\Package;

use App\Package\Abstracts\Relay;
use JsonSerializable;

/**
 * @mixin Relay
 */
final class Adapter implements JsonSerializable
{
    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @var Settings
     */
    private readonly Settings $settings;

    /**
     * @param Relay $relay
     */
    public function __construct(private readonly Relay $relay)
    {
        $this->settings = $this->relay->state()->getSettings();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->relay->getId();
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->getId(),
            'type'        => $this->relay->getType()->getName(),
            'enabled'     => $this->isEnabled(),
            'available'   => $this->isEnabled() ? $this->isAvailable() : null,
            'settings'    => $this->settings->collection()
                ->replaceValue('password', fn($v) => !empty($v) ? 'password' : ''),
            'pkgSettings' => $this->settings->collection()->except('password', 'username')->getKeys(),
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
     */
    public function saveSettings(): void
    {
        $this->settings->save();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->relay->state()->get('enabled', false);
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->cache['available'] ??= $this->relay->isAvailable();
    }
}