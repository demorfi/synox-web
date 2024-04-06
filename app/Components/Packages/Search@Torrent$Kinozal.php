<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category, Item\Torrent as TorrentItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\Document;

class Kinozal extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'https://kinozal.tv';

    /**
     * @inheritdoc
     */
    protected string $name = 'Kinozal';

    /**
     * @inheritdoc
     */
    protected string $description = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/browse.php?s={query}&g=0&page={page}';

    /**
     * @var string
     */
    protected string $urlDownload = 'https://dl.kinozal.tv/download.php?id=%d';

    /**
     * @var string
     */
    protected string $urlLogin = self::SITE_URL . '/takelogin.php';

    /**
     * @inheritdoc
     */
    protected int $numFirstPage = 0;

    /**
     * @var array|int[][]
     */
    protected static array $categories = [
        Category::AUDIO->value => [2, 3, 4, 5, 42],
        Category::VIDEO->value => [
            [1, 6, 7, 8, 9, 10, 11, 12, 13, 14],
            [15, 16, 17, 18, 20, 21, 22, 24, 35, 37],
            [38, 39, 45, 46, 47, 48, 49, 50]
        ],
        Category::APPLICATION->value => [32, 40, 41],
        Category::GAME->value => [23]
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
        if (!$document->find('a[href^="/logout"]')->count()) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username' => $this->getSetting('username'),
                    'password' => $this->getSetting('password')
                ]
            );
            return (bool)$this->document($response)->find('a[href^="/logout"]')->count();
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
        preg_match(
            '/\s+(?P<total>\d+)\s+/',
            (string)$page->find('.content .tables1 tr')->last()?->find('td')->text(),
            $matches
        );

        if (!isset($matches['total']) || !(int)trim($matches['total'])) {
            return 0;
        }

        return (int)($page->find('.paginator li')->eq(-2)?->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $page): Document
    {
        $href = str_replace(self::SITE_URL, '', $url);
        $row  = $page->find('.content .t_peer tr.bg .nam a[href="' . $href . '"]')
            ?->closest('tr');
        return $this->document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): iterable
    {
        foreach ($page->find('.content .t_peer tr.bg') as $item) {
            $url = $item->find('.nam a')->attr('href');
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

        $item->setFetchId($itemPage->find('td.nam a')->attr('href'));
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/(?P<id>\d+)\./i', $itemPage->find('td.bt img')->attr('src'), $matches);
        if (isset($matches['id']) && ($categoryId = (int)$matches['id']) >= 1) {
            if (($categoryName = Category::tryFromArray($categoryId, self::$categories)) !== null) {
                $item->setCategory($categoryName);
            }

            $propCategory = trim($rootPage->find('select[name="c"] option[value="' . $categoryId . '"]')->text());
            if (!empty($propCategory)) {
                $item->addProperty('Tracker category', $propCategory);
            }
        }

        $item->setTitle(trim($itemPage->find('td.nam > a')->text()));
        $item->setSize(
            str_replace(
                ['КБ', 'МБ', 'ГБ', 'ТБ'],
                ['Kb', 'Mb', 'Gb', 'Tb'],
                $itemPage->find('td.nam + td.s + td.s')->text()
            )
        );

        $item->setSeeds((int)$itemPage->find('td.sl_s')->text());
        $item->setPeers((int)$itemPage->find('td.sl_p')->text());

        // Date created torrent
        preg_match('/(?P<date>\d{2}\.\d{2}\.\d{4})/i', $itemPage->find('td.sl_p + td.s')->text(), $matches);
        $item->setDate((int)strtotime($matches['date'] ?: '0'));

        return $item;
    }
}