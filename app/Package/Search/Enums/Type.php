<?php declare(strict_types=1);

namespace App\Package\Search\Enums;

use App\Package\Interfaces\BaseEnum;
use App\Package\Search\Interfaces\Package;
use App\Package\Search\Item\{Text as TextItem, Torrent as TorrentItem};
use App\Package\Search\Content\{Text as TextContent, Torrent as TorrentContent};

enum Type: string implements BaseEnum
{
    case TORRENT = TorrentItem::class;

    case TEXT = TextItem::class;

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
     * @param Package $package
     * @return TextItem|TorrentItem
     */
    public function makeItem(Package $package): TextItem|TorrentItem
    {
        return new $this->value($package);
    }

    /**
     * @return TextContent|TorrentContent
     */
    public function makeContent(): TextContent|TorrentContent
    {
        return match ($this) {
            self::TEXT => new TextContent,
            self::TORRENT => new TorrentContent
        };
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