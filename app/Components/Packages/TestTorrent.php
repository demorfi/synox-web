<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Abstracts\Package;
use App\Enums\{Category, ContentType, ItemType};
use App\Interfaces\PackageContent;
use App\Package\{Content\Torrent as TorrentContent, Item\Torrent as TorrentItem};
use App\Package\Query;
use Generator;

class TestTorrent extends Package
{
    /**
     * @var string
     */
    private string $name = 'Test Torrent';

    /**
     * @var string
     */
    private string $shortDescription = 'Test search torrent';

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getItemType(): ItemType
    {
        return ItemType::TORRENT;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): ContentType
    {
        return ContentType::TORRENT;
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function hasAuth(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function search(Query $query): Generator
    {
        $maxResults = rand(6, 24);
        for ($i = 1; $i <= $maxResults; $i++) {
            sleep(1);
            $item = new TorrentItem($this);
            $item->setTitle($i . ' Test ' . $query->value);
            $item->setCategory($query->filter?->getById(Category::getId())->first() ?? Category::VIDEO);
            $item->addProperty('Info-1', 'Torrent 1 property ' . $i);
            $item->addProperty('Info-2', 'Torrent 2 property ' . $i);
            $item->setPeers(rand(1, 100));
            $item->setSeeds(rand(1, 100));
            $item->setSize(rand(1000000, 9999999));
            $item->setDate(date_create());
            $item->setFetchId('#fetch/?id=' . $this->name . '&fetch=' . $i);
            $item->setPageUrl('#page/?id=' . $this->name . '&page=' . $i);
            yield $item;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $id, TorrentContent|PackageContent $content): bool
    {
        $content->create('test', 'd8:announce');
        return $content->isAvailable();
    }
}
