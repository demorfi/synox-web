<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Text, Enums\Category, Item\Text as TextItem};
use DOMWrap\{Document, Element};

class Bananan extends Text
{
    /**
     * @var string
     */
    const SITE_URL = 'https://bananan.org';

    /**
     * @inheritdoc
     */
    protected string $name = 'Bananan';

    /**
     * @inheritdoc
     */
    protected string $description = 'Song text search ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/search?q={query}';

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
        foreach ($page->find('.songs.songs_b > h5')->slice(0, $total) as $item) {
            $item->wrapInner('<span class="b-title"></span>');
            $item->appendWith($item->following('p')->wrapInner('<span class="b-content"></span>'));
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

        $item->setFetchId($element->find('.b-content a')->attr('href'));
        $item->setPageUrl(self::SITE_URL . $item->getFetchId());
        $item->setCategory(Category::TEXT);

        // Clean trash content
        $element->find('.b-title .number')->destroy();
        $element->find('.b-content a, .b-content small')->destroy();

        // Title lyrics
        $title = preg_replace(['/[\r\n\t]+/', '/\s+/'], ' ', $element->find('.b-title')->text());
        $item->setTitle(trim($title));

        // Short lyrics
        $item->setDescription(trim($element->find('.b-content')->text()));

        // Artist lyrics
        $item->addProperty('Artist', trim($element->find('.b-title > a')->text()));
        $item->addProperty('Category', Category::AUDIO->value);

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function searchItemContent(Document $page): string
    {
        $text = $page->find('#text_or, #text_tr')
            ->map(fn($node) => trim(str_replace("\t", '', $node->text())))
            ->toArray();
        return implode("\n\n", $text);
    }
}