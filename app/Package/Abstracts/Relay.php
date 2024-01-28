<?php declare(strict_types=1);

namespace App\Package\Abstracts;

use App\Components\Settings;
use App\Package\Enums\Type;
use App\Package\Extension\Abstracts\Package as ExtensionPackageAbstract;
use App\Package\Search\Abstracts\Package as SearchPackageAbstract;
use BadMethodCallException;
use JsonSerializable;

/**
 * @mixin SearchPackageAbstract
 * @mixin ExtensionPackageAbstract
 */
abstract class Relay implements JsonSerializable
{
    /**
     * @param SearchPackageAbstract|ExtensionPackageAbstract $package
     * @param Settings                                       $settings
     */
    public function __construct(
        protected readonly SearchPackageAbstract|ExtensionPackageAbstract $package,
        protected readonly Settings $settings
    ) {
    }

    /**
     * @return SearchPackageAbstract|ExtensionPackageAbstract
     */
    public function instance(): SearchPackageAbstract|ExtensionPackageAbstract
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