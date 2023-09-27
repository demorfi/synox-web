<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Item\Text as TextItem;
use App\Prototype\Text;
use App\Enums\Category;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Generator;

class Bananan extends Text
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
    protected string $urlSearch = self::SITE_URL . '/search?q={query}';

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): Generator
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
     * @param Element $element
     * @return TextItem
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
        $item->setContent(trim($element->find('.b-content')->text()));

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
