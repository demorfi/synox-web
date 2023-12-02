<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Text, Enums\Category, Item\Text as TextItem};
use DOMWrap\{Document, Element};

class AllofLyrics extends Text
{
    /**
     * @var string
     */
    const SITE_URL = 'https://alloflyrics.cc';

    /**
     * @inheritdoc
     */
    protected string $name = 'AllofLyrics';

    /**
     * @inheritdoc
     */
    protected string $description = 'Song text search ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/search/?s={query}';

    /**
     * @inheritdoc
     */
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => [Category::TEXT]]);
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): iterable
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
     * @inheritdoc
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