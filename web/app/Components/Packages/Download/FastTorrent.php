<?php declare(strict_types=1);

namespace App\Components\Packages\Download;

use App\Package\Download\Item;
use App\Prototype\Download;
use Digua\Traits\Client;
use DOMWrap\Document;
use Generator;

class FastTorrent extends Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'http://fast-torrent.ru';

    /**
     * @var string
     */
    protected string $name = 'Fast-Torrent';

    /**
     * @inheritdoc
     */
    protected string $shortDescription = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/search/%s/50/%d.html';

    /**
     * @inheritdoc
     */
    protected function getTotalPagesFound(Document $document): int
    {
        preg_match('/\{"num_pages":\s?(?P<total>\d+)\s?}/', $document->html(), $matches);
        return (int)($matches['total'] ?? 0);
    }

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): array
    {
        $links = [];
        foreach ($document->find('.film-list .film-item') as $item) {
            $url = $item->find('a.film-download')->attr('href');
            if (!empty($url)) {
                $links[] = self::SITE_URL . $url;
            }
        }

        return $links;
    }

    /**
     * @inheritdoc
     */
    protected function createItem(string $url, Document $ItemDocument, Document $pageDocument): Generator
    {
        // Category torrent
        $category = $ItemDocument->find('.nav-menu > li > a')->text();

        // Torrents rows
        foreach ($ItemDocument->find('.torrent-row') as $row) {
            $item = new Item($this);

            // Url download torrent
            $download = $row->find('.torrent-info a[href*="/download/torrent/"]')->attr('href');
            $item->setFetchUrl(!empty($download) ? self::SITE_URL . $download : '');

            // Page torrent
            $item->setPageUrl($url);
            $item->setCategory($category);

            // Title torrent
            $title = $row->find('.torrent-info a[href^="/download/torrent/"]')->text();
            $title = trim(str_replace('.torrent', '', $title));
            if (!empty($title)) {
                $translation = trim((string)$row->find('.upload1 .c2')->first()?->text());
                $item->setTitle($title . ($translation ? ' [' . $translation . ']' : ''));
            }

            // Torrent size
            $item->setSize((float)$row->attr('size'));

            // Torrent count seeds
            preg_match('/(?P<seeds>\d+)/', $row->find('.upload1 .c19 em')->attr('class'), $matches);
            $item->setSeeds((int)($matches['seeds'] ?? 0));

            // Torrent count peers (Unknown)
            $item->setPeers(0);

            // Date created torrent
            $item->setDate((int)$row->attr('date'));

            yield $item;
        }
    }
}
