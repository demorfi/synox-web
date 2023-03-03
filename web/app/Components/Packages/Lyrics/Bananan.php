<?php declare(strict_types=1);

namespace App\Components\Packages\Lyrics;

use App\Prototype\Lyrics;
use App\Package\Lyrics\Item;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Generator;

class Bananan extends Lyrics
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://bananan.org';

    /**
     * @var string
     */
    protected string $name = 'Bananan';

    /**
     * @var string
     */
    protected string $shortDescription = 'Song text search ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/search?q=%s';

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): Generator
    {
        $total = 20;
        foreach ($document->find('.songs.songs_b > h5')->slice(0, $total) as $item) {
            $item->wrapInner('<span class="b-title"></span>');
            $item->appendWith($item->following('p')->wrapInner('<span class="b-content"></span>'));
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
        $url  = self::SITE_URL . $element->find('.b-content a')->attr('href');

        // Clean trash content
        $element->find('.b-title .number')->destroy();
        $element->find('.b-content a, .b-content small')->destroy();

        // Title lyrics
        $title = preg_replace(['/[\r\n\t]+/', '/\s+/'], ' ', $element->find('.b-title')->text());
        $item->setTitle(trim($title));

        // Short lyrics
        $item->setContent(trim($element->find('.b-content')->text()));

        // Artist lyrics
        $item->setArtist(trim($element->find('.b-title > a')->text()));

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
        $text = $document->find('#text_or, #text_tr')
            ->map(fn($node) => trim(str_replace("\t", '', $node->text())))
            ->toArray();
        return implode("\n\n", $text);
    }
}
