<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category, Item\Torrent as TorrentItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\Document;
use Generator;

class Rutor extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'https://rutor.org';

    /**
     * @inheritdoc
     */
    protected string $name = 'Rutor';

    /**
     * @inheritdoc
     */
    protected string $description = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/search/{page}/0/000/0/{query}';

    /**
     * @var string
     */
    protected string $urlDownload = 'https://d.rutor.org/download/%d';

    /**
     * @var string
     */
    protected string $urlLogin = self::SITE_URL . '/login/do';

    /**
     * @var array|int[][]
     */
    protected static array $categories = [
        Category::AUDIO->value => [2],
        Category::VIDEO->value => [1, 4, 5, 6, 7, 10, 12, 16],
        Category::APPLICATION->value => [9],
        Category::GAME->value => [8]
    ];

    /**
     * @inheritdoc
     */
    public function hasAuth(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    protected function isAvailableAccount(): bool
    {
        $client = $this->client();
        $client->useCookie($this->getId());

        $document = $this->document($this->sendGet($client, self::SITE_URL));
        if (!$document->find('.logout')->count()) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username' => $this->getSetting('username'),
                    'password' => $this->getSetting('password')
                ]
            );
            return (bool)$this->document($response)->find('.logout')->count();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => array_keys(self::$categories)]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountPagesFound(Document $page): int
    {
        preg_match('/^\s?(?P<total>\d+)\s+/', $page->find('title')->text(), $matches);
        if (!isset($matches['total']) || !(int)trim($matches['total'])) {
            return 0;
        }

        return max(((int)$page->find('#content p')->first()?->find('a[href]')->count()) - 1, 1);
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $page): Document
    {
        $href = str_replace(self::SITE_URL, '', $url);
        $row  = $page->find('#index table .backgr ~ tr a[href="' . $href . '"]')
            ?->closest('tr');
        return $this->document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): Generator
    {
        foreach ($page->find('#index table .backgr ~ tr') as $item) {
            $url = $item->find('a[href^="/torrent/"]')->attr('href');
            if (!empty($url)) {
                yield self::SITE_URL . $url;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function buildFetchUrl(string $id): string
    {
        return sprintf($this->urlDownload, filter_var($id, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * @inheritdoc
     */
    protected function buildItem(string $url, Document $itemPage, Document $rootPage): TorrentItem
    {
        $item = $this->makeItem();

        $item->setFetchId(filter_var($itemPage->find('a.downgif')->attr('href'), FILTER_SANITIZE_NUMBER_INT));
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/search_category\s+=\s+(?P<id>\d+);/i', $rootPage->text(), $matches);
        if (isset($matches['id']) && ($categoryId = (int)$matches['id']) >= 1) {
            if (($categoryName = Category::tryFromArray($categoryId, self::$categories)) !== null) {
                $item->setCategory($categoryName);
            }

            $propCategory = trim($rootPage->find('#category_id option[value="' . $categoryId . '"]')->text());
            if (!empty($propCategory)) {
                $item->addProperty('Tracker category', $propCategory);
            }
        }

        $item->setTitle(trim($itemPage->find('a[href^="/torrent/"]')->text()));
        $item->setSize((string)$itemPage->find('td[align="right"]')->eq(-1)?->text());

        $item->setSeeds(
            (int)filter_var(
                $itemPage->find('td > span')->first()?->text(),
                FILTER_SANITIZE_NUMBER_INT
            )
        );

        $item->setPeers(
            (int)filter_var(
                $itemPage->find('td > span')->last()?->text(),
                FILTER_SANITIZE_NUMBER_INT
            )
        );

        // Date created torrent
        $months = ['', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июл', 'Июн', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
        $date   = date_create_from_format(
            'd m y',
            str_replace($months, array_flip($months), (string)$itemPage->find('td')?->first()->text())
        );
        if ($date !== false) {
            $item->setDate($date);
        }

        return $item;
    }
}