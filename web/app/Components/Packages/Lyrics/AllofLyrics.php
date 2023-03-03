<?php declare(strict_types=1);

namespace App\Components\Packages\Lyrics;

use App\Prototype\Lyrics;
use App\Package\Lyrics\Item;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Generator;

class AllofLyrics extends Lyrics
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://alloflyrics.cc';

    /**
     * @var string
     */
    protected string $name = 'AllofLyrics';

    /**
     * @var string
     */
    protected string $shortDescription = 'Song text search ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/search/?s=%s';

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): Generator
    {
        $total = 20;
        foreach ($document->find('.container table td a[href^="/song/"]')->slice(0, $total) as $item) {
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
        $url  = self::SITE_URL . $element->attr('href');

        // Title lyrics
        $item->setTitle(trim($element->text()));

        // Artist lyrics
        $matches = preg_split('/\s+-\s+|:\s+/', $item->getTitle(), 2, PREG_SPLIT_NO_EMPTY);
        [, $artist] = array_pad(array_reverse($matches), 2, '');
        $item->setArtist($artist);

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
        return $document->find('.jumbotron .container > p')->text();
    }
}
