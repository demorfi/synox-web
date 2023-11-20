<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category};
use DOMWrap\Document;
use Generator;

class FastTorrent extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'http://fast-torrent.ru';

    /**
     * @inheritdoc
     */
    protected string $name = 'Fast-Torrent';

    /**
     * @inheritdoc
     */
    protected string $description = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/search/{query}/50/{page}.html';

    /**
     * @inheritdoc
     */
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => [Category::VIDEO]]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountPagesFound(Document $page): int
    {
        preg_match('/\{"num_pages":\s?(?P<total>\d+)\s?}/', $page->html(), $matches);
        return (int)($matches['total'] ?? 0);
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): Generator
    {
        foreach ($page->find('.film-list .film-item') as $item) {
            $url = $item->find('a.film-download')->attr('href');
            if (!empty($url)) {
                yield self::SITE_URL . $url;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function buildFetchUrl(string $id): string
    {
        return self::SITE_URL . $id;
    }

    /**
     * @inheritdoc
     */
    protected function buildItem(string $url, Document $itemPage, Document $rootPage): Generator
    {
        // Category torrent
        $propCategory = $itemPage->find('.nav-menu > li > a')->text();

        // Torrents rows
        foreach ($itemPage->find('.torrent-row') as $row) {
            $item = $this->makeItem();

            $item->setFetchId($row->find('.torrent-info a[href*="/download/torrent/"]')->attr('href'));
            $item->setPageUrl($url);
            $item->setCategory(Category::VIDEO);

            // Category torrent
            if (!empty($propCategory)) {
                $item->addProperty('Tracker category', $propCategory);
            }

            // Title torrent
            $title = $row->find('.torrent-info a[href^="/download/torrent/"]')->text();
            $title = trim(str_replace('.torrent', '', $title));
            if (!empty($title)) {
                $translation = trim((string)$row->find('.upload1 .c2')->first()?->text());
                $item->setTitle($title . ($translation ? ' [' . $translation . ']' : ''));
            }

            // Torrent count seeds
            preg_match('/(?P<seeds>\d+)/', $row->find('.upload1 .c19 em')->attr('class'), $matches);
            $item->setSeeds((int)($matches['seeds'] ?? 0));

            $item->setSize((float)$row->attr('size'));
            $item->setPeers(0);
            $item->setDate((int)$row->attr('date'));

            yield $item;
        }
    }
}