<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\Abstracts\Package;
use App\Package\Search\Enums\{Type, Category};
use App\Package\Search\{Query, Filter};
use App\Package\Search\Item\Torrent as TorrentItem;
use App\Package\Search\Content\Torrent as TorrentContent;

class TestTorrent extends Package
{
    /**
     * @var Type
     */
    private Type $type = Type::TORRENT;

    /**
     * @var string
     */
    private string $name = 'Test Torrent';

    /**
     * @var string
     */
    private string $description = 'Test search torrent';

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
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description;
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
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => [Category::AUDIO, Category::VIDEO, Category::APPLICATION, Category::GAME]]);
    }

    /**
     * @inheritdoc
     */
    public function search(Query $query): iterable
    {
        $maxResults = rand(6, 24);
        for ($i = 1; $i <= $maxResults; $i++) {
            usleep(250000);
            $item = new TorrentItem($this);
            $item->setTitle($i . ' Test ' . $query->value);
            $item->setCategory($query->filter?->getById(Category::getFilterId())->first() ?? Category::VIDEO);
            $item->addProperty('Info-1', 'Torrent 1 property ' . $i);
            $item->addProperty('Info-2', 'Torrent 2 property ' . $i);
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
    public function fetch(Query $query): TorrentContent
    {
        $content = 'ZDEzOmNyZWF0aW9uIGRhdGVpMTQ0OTczMDI4Nzg0MmU4OmVuY29kaW5nNTpVVEYtODQ6aW5mb2Q1OmZpbGVzbGQ2Omxlbmd0aGkxZTQ6cGF0aGw1O'
            . 'jEudHh0ZWVkNjpsZW5ndGhpMmU0OnBhdGhsNToyLnR4dGVlZDY6bGVuZ3RoaTNlNDpwYXRobDU6My50eHRlZWU0Om5hbWU3Om51bWJlcn'
            . 'MxMjpwaWVjZSBsZW5ndGhpMTYzODRlNjpwaWVjZXMyMDofdGSOUKamcI7FSrMnoWPVU2t87WVl';
        return $this->getType()->makeContent()->create('', base64_decode($content));
    }
}