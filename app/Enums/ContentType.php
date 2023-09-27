<?php declare(strict_types=1);

namespace App\Enums;

use App\Package\Content\{Text, Torrent};

enum ContentType: string
{
    case TEXT = Text::class;

    case TORRENT = Torrent::class;

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
     * @return Text|Torrent
     */
    public function make(): Text|Torrent
    {
        return new $this->value;
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
    public function getName(): string
    {
        return ucfirst(strtolower($this->name));
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return strtolower($this->name);
    }

    /**
     * @return FileExtension
     */
    public function extension(): FileExtension
    {
        return match ($this) {
            self::TEXT => FileExtension::TEXT,
            self::TORRENT => FileExtension::TORRENT
        };
    }
}
