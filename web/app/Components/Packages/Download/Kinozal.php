<?php declare(strict_types=1);

namespace App\Components\Packages\Download;

use App\Package\Download\Item;
use App\Prototype\Download;
use App\Enums\Category;
use App\Components\Helper;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Traits\Client;
use DOMWrap\Document;
use Generator;

class Kinozal extends Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://kinozal.tv';

    /**
     * @var string
     */
    protected string $name = 'Kinozal';

    /**
     * @inheritdoc
     */
    protected string $shortDescription = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/browse.php?s=%s&g=0&page=%d';

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
    protected int $firstPage = 0;

    /**
     * @inheritdoc
     */
    protected static array $filters = [
        Category::class => [
            Category::AUDIO->name        => '&c=1004',
            Category::VIDEO->name        => '&c=1002',
            Category::APPLICATIONS->name => '&c=32',
            Category::GAMES->name        => '&c=23'
        ]
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
     */
    protected function isAvailableAccount(): bool
    {
        $client = new CurlClient;
        $client->useCookie($this->getId());

        $document = Helper::document($this->sendGet($client, self::SITE_URL));
        if (!$document->find('a[href^="/logout"]')->count()) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username' => $this->getSetting('username'),
                    'password' => $this->getSetting('password')
                ]
            );
            return (bool)Helper::document($response)->find('a[href^="/logout"]')->count();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getTotalPagesFound(Document $document): int
    {
        preg_match(
            '/\s+(?P<total>\d+)\s+/',
            (string)$document->find('.content .tables1 tr')->last()?->find('td')->text(),
            $matches
        );

        if (!isset($matches['total']) || !(int)trim($matches['total'])) {
            return 0;
        }

        return (int)($document->find('.paginator li')->eq(-2)?->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $document): Document
    {
        $href = str_replace(self::SITE_URL, '', $url);
        $row  = $document->find('.content .t_peer tr.bg .nam a[href="' . $href . '"]')
            ?->closest('tr');
        return Helper::document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): array
    {
        $links = [];
        foreach ($document->find('.content .t_peer tr.bg') as $item) {
            $url = $item->find('.nam a')->attr('href');
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
        $download = $ItemDocument->find('td.nam a')->attr('href');
        $item->setFetchUrl(
            !empty($download)
                ? sprintf($this->urlDownload, filter_var($download, FILTER_SANITIZE_NUMBER_INT))
                : ''
        );

        // Page torrent
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/(?P<id>\d+)\./i', $ItemDocument->find('td.bt img')->attr('src'), $matches);
        if (isset($matches['id']) && !empty($matches['id'])) {
            $category = $pageDocument->find('select[name="c"] option[value="' . (int)$matches['id'] . '"]')->text();
            $item->setCategory(trim($category));
        }

        // Title torrent
        $item->setTitle(trim($ItemDocument->find('td.nam > a')->text()));

        // Torrent size
        $item->setSize(
            str_replace(
                ['КБ', 'МБ', 'ГБ', 'ТБ'],
                ['Kb', 'Mb', 'Gb', 'Tb'],
                $ItemDocument->find('td.nam + td.s + td.s')->text()
            )
        );

        // Torrent count seeds
        $item->setSeeds((int)$ItemDocument->find('td.sl_s')->text());

        // Torrent count peers
        $item->setPeers((int)$ItemDocument->find('td.sl_p')->text());

        // Date created torrent
        preg_match('/(?P<date>\d{2}\.\d{2}\.\d{4})/i', $ItemDocument->find('td.sl_p + td.s')->text(), $matches);
        $item->setDate((int)strtotime($matches['date'] ?: '0'));

        yield $item;
    }
}
