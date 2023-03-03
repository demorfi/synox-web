<?php declare(strict_types=1);

namespace App\Components\Packages\Download;

use App\Enums\Category;
use App\Package\Download\Item;
use App\Prototype\Download;
use App\Components\Helper;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Traits\Client;
use DOMWrap\Document;
use Generator;

class Rutor extends Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://rutor.org';

    /**
     * @var string
     */
    protected string $name = 'Rutor';

    /**
     * @inheritdoc
     */
    protected string $shortDescription = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/search/%d/0/000/0/%s';

    /**
     * @inheritdoc
     */
    protected static array $filters = [
        Category::class => [
            Category::AUDIO->name        => ['search/(.+)/(.+)/(.+)/(.+)/', 'search/${1}/2/${3}/${4}/'],
            Category::VIDEO->name        => ['search/(.+)/(.+)/(.+)/(.+)/', 'search/${1}/1/${3}/${4}/'],
            Category::APPLICATIONS->name => ['search/(.+)/(.+)/(.+)/(.+)/', 'search/${1}/9/${3}/${4}/'],
            Category::GAMES->name        => ['search/(.+)/(.+)/(.+)/(.+)/', 'search/${1}/8/${3}/${4}/']
        ]
    ];

    /**
     * @inheritdoc
     */
    protected function buildSearchUrl(string $name, int $page = 1): string
    {
        return sprintf($this->buildFiltersUrl($this->urlSearch), $page, urlencode($name));
    }

    /**
     * @inheritdoc
     */
    protected function getTotalPagesFound(Document $document): int
    {
        preg_match('/^\s?(?P<total>\d+)\s+/', $document->find('title')->text(), $matches);
        if (!isset($matches['total']) || !(int)trim($matches['total'])) {
            return 0;
        }

        return max(((int)$document->find('#content p')->first()?->find('a[href]')->count()) - 1, 1);
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $document): Document
    {
        $href = str_replace(self::SITE_URL, '', $url);
        $row  = $document->find('#index table .backgr ~ tr a[href="' . $href . '"]')
            ?->closest('tr');
        return Helper::document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): array
    {
        $links = [];
        foreach ($document->find('#index table .backgr ~ tr') as $item) {
            $url = $item->find('a[href^="/torrent/"]')->attr('href');
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
        $item = new Item($this);

        // Url download torrent
        $download = (string)$ItemDocument->find('a.downgif')->attr('href');
        $item->setFetchUrl($download);

        // Page torrent
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/search_category\s+=\s+(?P<id>\d+);/i', $pageDocument->text(), $matches);
        if (isset($matches['id'])) {
            $category = $pageDocument->find('#category_id option[value="' . (int)$matches['id'] . '"]')->text();
            $item->setCategory(trim($category));
        }

        // Title torrent
        $item->setTitle(trim($ItemDocument->find('a[href^="/torrent/"]')->text()));

        // Torrent count seeds
        $item->setSeeds(
            (int)filter_var(
                $ItemDocument->find('td > span')->first()?->text(),
                FILTER_SANITIZE_NUMBER_INT
            )
        );

        // Torrent count peers
        $item->setPeers(
            (int)filter_var(
                $ItemDocument->find('td > span')->last()?->text(),
                FILTER_SANITIZE_NUMBER_INT
            )
        );

        // Torrent size
        $item->setSize((string)$ItemDocument->find('td[align="right"]')->eq(-1)?->text());

        // Date created torrent
        $months = ['', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июл', 'Июн', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
        $date   = str_replace($months, array_flip($months), (string)$ItemDocument->find('td')?->first()->text());
        if (!empty($date)) {
            $item->setDate(date_create_from_format('d m y', $date)->getTimestamp());
        }

        yield $item;
    }
}
