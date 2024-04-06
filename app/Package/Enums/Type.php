<?php declare(strict_types=1);

namespace App\Package\Enums;

use App\Components\Settings;
use App\Package\Interfaces\BaseEnum;
use App\Package\Extension\{
    Enums\Subtype as ExtensionSubtype,
    Interfaces\Package as ExtensionPackageInterface,
    Relay as ExtensionRelay
};
use App\Package\Search\{
    Enums\Subtype as SearchSubtype,
    Interfaces\Package as SearchPackageInterface,
    Relay as SearchRelay
};

enum Type: string implements BaseEnum
{
    case SEARCH = SearchSubtype::class;

    case EXTENSION = ExtensionSubtype::class;

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return strtolower($this->name);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return ucfirst(strtolower($this->name));
    }

    /**
     * @inheritdoc
     */
    public static function tryName(string $name): ?self
    {
        $name = strtoupper($name);
        foreach (self::cases() as $case) {
            if ($case->name == $name) {
                return $case;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return ExtensionSubtype|SearchSubtype|null
     */
    public function trySubtypeName(string $name): ExtensionSubtype|SearchSubtype|null
    {
        return match ($this) {
            self::EXTENSION => ExtensionSubtype::tryName($name),
            self::SEARCH => SearchSubtype::tryName($name)
        };
    }

    /**
     * @return string
     */
    public function getInterface(): string
    {
        return match ($this) {
            self::EXTENSION => ExtensionPackageInterface::class,
            self::SEARCH => SearchPackageInterface::class
        };
    }

    /**
     * @param ExtensionPackageInterface|SearchPackageInterface $package
     * @param Settings $settings
     * @return ExtensionRelay|SearchRelay
     */
    public function makeRelay(ExtensionPackageInterface|SearchPackageInterface $package, Settings $settings): ExtensionRelay|SearchRelay
    {
        return match ($this) {
            self::EXTENSION => new ExtensionRelay($package, $settings),
            self::SEARCH => new SearchRelay($package, $settings),
        };
    }
}