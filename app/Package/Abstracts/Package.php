<?php declare(strict_types=1);

namespace App\Package\Abstracts;

use App\Components\Settings;
use App\Package\Interfaces\Package as PackageInterface;
use Digua\Enums\FileExtension;
use Digua\Components\ArrayCollection;

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
        return $this->settings->get($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return self
     */
    final protected function setSetting(string $name, mixed $value): self
    {
        $this->settings->set($name, $value);
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