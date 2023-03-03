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

class RuTracker extends Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://rutracker.org/forum';

    /**
     * @var string
     */
    protected string $name = 'Rutracker';

    /**
     * @inheritdoc
     */
    protected string $shortDescription = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $urlSearch = self::SITE_URL . '/tracker.php?nm=%s';

    /**
     * @var string
     */
    protected string $urlLogin = self::SITE_URL . '/login.php';

    /**
     * @inheritdoc
     */
    protected static array $filters = [
        Category::class => [
            Category::AUDIO->name        => '&f=1330,1702,1704,1706,1708,1710,1712,1736,1753,2270,2329,2500,2502,429',
            Category::VIDEO->name        => '&f=1247,1457,194,2201,2339,312,313',
            Category::APPLICATIONS->name => '&f=1025,1028,1029,1031,1032,1381,1536',
            Category::GAMES->name        => '&f=127,2118,2478,2480,2481,357,50,52,53,54,635,908'
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

        $document = Helper::document($this->sendGet($client, self::SITE_URL . '/index.php'));
        if (!$document->find('#logged-in-username')->count()) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'login_username' => $this->getSetting('username'),
                    'login_password' => $this->getSetting('password'),
                    'ses_short'      => '0',
                    'login'          => ''
                ]
            );
            return (bool)Helper::document($response)->find('.logged-in-as-uname')->count();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getTotalPagesFound(Document $document): int
    {
        preg_match(
            '/(?P<result>\d+)\s+\(/',
            $document->find('#main_content_wrap > table td p + p')->text(),
            $matches
        );

        if (!isset($matches['result']) || !(int)trim($matches['result'])) {
            return 0;
        }

        return (int)($document->find('.bottom_info p b + b')->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getNextPage(CurlClient $client, string $url, Document $document): ?Document
    {
        $nextPage = $document->find('#main_content_wrap > table h1 + p a')->last()?->attr('href');
        return !empty($nextPage) ? $this->getPage($client, self::SITE_URL . '/' . $nextPage) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $document): Document
    {
        $href = str_replace(self::SITE_URL . '/', '', $url);
        $row  = $document->find('.forumline tr.hl-tr a.tLink[href="' . $href . '"]')
            ?->closest('tr');
        return Helper::document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): array
    {
        $links = [];
        foreach ($document->find('.forumline tr.hl-tr') as $item) {
            $url = $item->find('a.tLink')->attr('href');
            if (!empty($url)) {
                $links[] = self::SITE_URL . '/' . $url;
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
        $download = $ItemDocument->find('td.tor-size a.tr-dl')->attr('href');
        $item->setFetchUrl(!empty($download) ? (self::SITE_URL . '/' . $download) : '');

        // Page torrent
        $item->setPageUrl($url);

        // Category torrent
        $category = trim($ItemDocument->find('.f-name a.ts-text')->text());
        $item->setCategory($category);

        // Title torrent
        $item->setTitle(trim($ItemDocument->find('.t-title a.tLink')->text()));

        // Torrent size
        $item->setSize((float)$ItemDocument->find('td.tor-size')->attr('data-ts_text'));

        // Torrent count seeds
        $item->setSeeds((int)$ItemDocument->find('td .seedmed')->text());

        // Torrent count peers
        $item->setPeers((int)$ItemDocument->find('td.leechmed')->text());

        // Date created torrent
        $item->setDate((int)$ItemDocument->find('td')->last()?->attr('data-ts_text'));

        yield $item;
    }
}
