<?php declare(strict_types=1);

namespace App\Package\Abstracts;

use App\Components\Settings;
use App\Package\Interfaces\Package as PackageInterface;
use Digua\Components\ArrayCollection;
use Digua\Exceptions\Storage as StorageException;

abstract class Package implements PackageInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(private readonly Settings $settings)
    {
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getRequires(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function wakeup(): void
    {
    }

    /**
     * @param array $array
     * @return ArrayCollection
     */
    final protected function collection(array $array): ArrayCollection
    {
        return ArrayCollection::make($array);
    }

    /**
     * @param string $name
     * @param mixed  $default If request key not found it return default value
     * @return mixed
     */
    final protected function getSetting(string $name, mixed $default = null): mixed
    {
        $setting = $this->settings->get($name, $default);
        return is_array($setting) && isset($setting['value']) ? $setting['value'] ?: $default : $setting;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return self
     * @throws StorageException
     */
    final protected function setSetting(string $name, mixed $value): self
    {
        $this->settings->set($name, $value);
        $this->settings->save();
        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @param mixed  $value
     * @param string $label
     * @param array  $params
     * @return self
     * @throws StorageException
     */
    final protected function addSetting(string $type, string $name, mixed $value, string $label, array $params = []): self
    {
        if (!$this->settings->has($name)) {
            $this->settings->set($name, compact('type', 'value', 'label', 'params'));
            $this->settings->save();
        }

        return $this;
    }

    /**
     * @return string
     */
    final public function getId(): string
    {
        return $this->settings->getId();
    }
}