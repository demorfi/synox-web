<?php declare(strict_types=1);

namespace App\Abstracts;

use App\Interfaces\Package as PackageInterface;
use Digua\Components\DataFile;
use Digua\Enums\FileExtension;

abstract class Package implements PackageInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(private readonly DataFile $settings)
    {
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return bool false
     */
    public function __call(string $name, array $arguments): bool
    {
        return false;
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
    final protected function getId(): string
    {
        return basename($this->settings->getName(), FileExtension::JSON->value);
    }
}
