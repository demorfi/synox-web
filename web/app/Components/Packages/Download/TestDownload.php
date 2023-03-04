<?php declare(strict_types=1);

namespace App\Components\Packages\Download;

use App\Abstracts\Package;
use App\Interfaces\Download as DownloadInterface;
use App\Enums\Category;
use App\Package\PackageQuery;
use App\Package\Download\{Item, Torrent};
use Generator;

class TestDownload extends Package implements DownloadInterface
{
    /**
     * @var string
     */
    private string $name = 'Test Download';

    /**
     * @var string
     */
    private string $shortDescription = 'Test download';

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
    public function search(PackageQuery $query): Generator
    {
        $maxResults = rand(6, 24);
        for ($i = 1; $i <= $maxResults; $i++) {
            $item = new Item($this);
            $item->setTitle($i . ' Test ' . $query->value);
            $item->setCategory($query->filter?->getById(Category::getId())?->value ?? 'Test category');
            $item->setPeers(rand(1, 100));
            $item->setSeeds(rand(1, 100));
            $item->setSize(rand(1000000, 9999999));
            $item->setDate(date_create());
            $item->setFetchUrl('http://synox.loc/download/fetch/?id=' . $this->name . '&fetch=' . $i);
            $item->setPageUrl('http://synox.loc/download/?id=' . $this->name . '&page=' . $i);
            yield $item;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $url, Torrent $file): bool
    {
        $file->create('test', 'd8:announce');
        return $file->isAvailable();
    }
}
