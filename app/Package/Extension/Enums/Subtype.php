<?php declare(strict_types=1);

namespace App\Package\Extension\Enums;

use App\Package\Interfaces\BaseEnum;

enum Subtype: string implements BaseEnum
{
    case BASE = 'Base';

    case HOOK = 'Hook';

    /**
     * @return string
     */
    public function getId(): string
    {
        return strtolower($this->name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return ucfirst(strtolower($this->name));
    }

    /**
     * @param string $name
     * @return ?self
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
}