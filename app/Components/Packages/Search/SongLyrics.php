<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Text, Enums\Category, Item\Text as TextItem};
use DOMWrap\{Document, Element};

class SongLyrics extends Text
{
    /**
     * @var string
     */
    const SITE_URL = 'https://www.songlyrics.com';

    /**
     * @inheritdoc
     */
    protected string $name = 'SongLyrics';

    /**
     * @inheritdoc
     */
    protected string $description = 'Song text search ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/index.php?searchW={query}&section=search';

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
     * @inheritdoc
     */
    protected function buildItem(Element $element): TextItem
    {
        $item = $this->makeItem();

        $item->setPageUrl($element->find('h3 a')->attr('href'));
        $item->setFetchId(parse_url($item->getPageUrl())['path']);
        $item->setCategory(Category::TEXT);
        $item->setTitle(trim($element->find('h3 a')->text()));

        // Short lyrics
        $item->setDescription(trim((string)$element->find('.serpdesc-2 p + p')->last()?->text()));

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