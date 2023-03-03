<?php declare(strict_types=1);

namespace App\Components\Packages\Lyrics;

use App\Prototype\Lyrics;
use App\Package\Lyrics\Item;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Generator;

class SongLyrics extends Lyrics
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://www.songlyrics.com';

    /**
     * @var string
     */
    protected string $name = 'SongLyrics';

    /**
     * @var string
     */
    protected string $shortDescription = 'Song text search ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/index.php?searchW=%s&section=search';

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): Generator
    {
        $total = 20;
        foreach ($document->find('.wrapper-inner .serpresult:not(.noresults)')->slice(0, $total) as $item) {
            yield $item;
        }
    }

    /**
     * Create item.
     *
     * @param Element $element
     * @return Item
     */
    protected function createItem(Element $element): Item
    {
        $item = new Item($this);
        $url  = $element->find('h3 a')->attr('href');

        // Title lyrics
        $item->setTitle(trim($element->find('h3 a')->text()));

        // Short lyrics
        $item->setContent(trim((string)$element->find('.serpdesc-2 p + p')->last()?->text()));

        // Artist lyrics
        $item->setArtist(trim((string)$element->find('.serpdesc-2 p a')->first()?->text()));

        // Page lyric
        $item->setPageUrl($url);

        // Page text lyric
        $item->setFetchUrl($url);

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function searchItemContent(Document $document): string
    {
        return $document->find('#songLyricsDiv')->text();
    }
}
