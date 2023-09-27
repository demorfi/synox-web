<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Item\Text as TextItem;
use App\Prototype\Text;
use App\Enums\Category;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Generator;

class SongLyrics extends Text
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
    protected string $urlSearch = self::SITE_URL . '/index.php?searchW={query}&section=search';

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): Generator
    {
        $total = 20;
        foreach ($page->find('.wrapper-inner .serpresult:not(.noresults)')->slice(0, $total) as $item) {
            yield $item;
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
     * @param Element $element
     * @return TextItem
     */
    protected function buildItem(Element $element): TextItem
    {
        $item = $this->makeItem();

        $item->setPageUrl($element->find('h3 a')->attr('href'));
        $item->setFetchId(parse_url($item->getPageUrl())['path']);
        $item->setCategory(Category::TEXT);
        $item->setTitle(trim($element->find('h3 a')->text()));

        // Short lyrics
        $item->setContent(trim((string)$element->find('.serpdesc-2 p + p')->last()?->text()));

        // Artist lyrics
        $item->addProperty('Artist', trim((string)$element->find('.serpdesc-2 p a')->first()?->text()));
        $item->addProperty('Category', Category::AUDIO->value);

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function searchItemContent(Document $page): string
    {
        return $page->find('#songLyricsDiv')->text();
    }
}
