<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Item\Text as TextItem;
use App\Prototype\Text;
use App\Enums\Category;
use DOMWrap\Document;
use DOMWrap\Element;
use Generator;

class AllofLyrics extends Text
{
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
    protected string $urlSearch = self::SITE_URL . '/search/?s={query}';

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): Generator
    {
        $total = 20;
        foreach ($page->find('.container table td a[href^="/song/"]')->slice(0, $total) as $item) {
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

        $item->setFetchId($element->attr('href'));
        $item->setPageUrl(self::SITE_URL . $item->getFetchId());
        $item->setCategory(Category::TEXT);
        $item->setTitle(trim($element->text()));

        // Artist lyrics
        $matches = preg_split('/\s+-\s+|:\s+/', $item->getTitle(), 2, PREG_SPLIT_NO_EMPTY);
        [, $artist] = array_pad(array_reverse($matches), 2, '');
        $item->addProperty('Artist', $artist);
        $item->addProperty('Category', Category::AUDIO->value);

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function searchItemContent(Document $page): string
    {
        return $page->find('.jumbotron .container > p')->text();
    }
}
