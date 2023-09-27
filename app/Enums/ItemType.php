<?php declare(strict_types=1);

namespace App\Enums;

use App\Package\Item\{Text, Torrent};
use App\Interfaces\Package;

enum ItemType: string
{
    case TORRENT = Torrent::class;

    case TEXT = Text::class;

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

    /**
     * @param Package $package
     * @return Text|Torrent
     */
    public function make(Package $package): Text|Torrent
    {
        return new $this->value($package);
    }

    /**
     * @return string
     */
    public function getInterface(): string
    {
        return $this->value;
    }

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
     * @return ContentType
     */
    public function contentType(): ContentType
    {
        return match ($this) {
            self::TEXT => ContentType::TEXT,
            self::TORRENT => ContentType::TORRENT
        };
    }
}
