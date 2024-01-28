<?php declare(strict_types=1);

namespace App\Package\Abstracts;

use App\Components\Settings;
use App\Package\Enums\Type;
use App\Package\Extension\Interfaces\Package as ExtensionPackageInterface;
use App\Package\Search\Interfaces\Package as SearchPackageInterface;
use BadMethodCallException;
use JsonSerializable;

/**
 * @mixin SearchPackageInterface
 * @mixin ExtensionPackageInterface
 */
abstract class Relay implements JsonSerializable
{
    /**
     * @param SearchPackageInterface|ExtensionPackageInterface $package
     * @param Settings                                       $settings
     */
    public function __construct(
        protected readonly SearchPackageInterface|ExtensionPackageInterface $package,
        protected readonly Settings $settings
    ) {
    }

    /**
     * @return SearchPackageInterface|ExtensionPackageInterface
     */
    public function instance(): SearchPackageInterface|ExtensionPackageInterface
    {
        return $this->package;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'subtype'     => $this->package->getType()->getName(),
            'name'        => $this->package->getName(),
            'description' => $this->package->getDescription(),
            'version'     => $this->package->getVersion()
        ];
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->package, $name)) {
            throw new BadMethodCallException(sprintf('Method (%s) does not exist!', $name));
        }

        return $this->package->$name(...$arguments);
    }

    /**
     * @return Type
     */
    abstract public function getType(): Type;
}