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

class NnmClub extends Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://nnmclub.to/forum';

    /**
     * @var string
     */
    protected string $name = 'NNM Club';

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
            Category::AUDIO->name        => '&f=313,330,962,965,337,963,961,1165,1161,1325,976,983,1159',
            Category::VIDEO->name        => '&f=218,954,885,912,227,1296,1150,321,272',
            Category::APPLICATIONS->name => '&f=503,512,562,514,517,520,521,523,532,530,526,764,820,552',
            Category::GAMES->name        => '&f=410,411,415,746,413,970,390,386'
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

        $document = Helper::document($this->sendGet($client, self::SITE_URL . '/tracker.php'));
        if (!$document->find('a[href^="login.php?logout"]')->count()) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username'  => $this->getSetting('username'),
                    'password'  => $this->getSetting('password'),
                    'autologin' => '1',
                    'login'     => ''
                ]
            );
            return (bool)Helper::document($response)->find('a[href^="login.php?logout"]')->count();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getTotalPagesFound(Document $document): int
    {
        preg_match('/(?P<result>\d+)\s+\(max/i', $document->find('#search_form table .nav')->text(), $matches);
        if (!isset($matches['result']) || !(int)trim($matches['result'])) {
            return 0;
        }

        return (int)($document->find('#search_form table td span b + b')->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getNextPage(CurlClient $client, string $url, Document $document): ?Document
    {
        $nextPage = $document->find('#search_form table .nav b + a')->attr('href');
        return !empty($nextPage) ? $this->getPage($client, self::SITE_URL . '/' . $nextPage) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $document): Document
    {
        $href = str_replace(self::SITE_URL . '/', '', $url);
        $row  = $document->find('.forumline.tablesorter tbody tr .genmed a.genmed[href="' . $href . '"]')
            ?->closest('tr');
        return Helper::document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): array
    {
        $links = [];
        foreach ($document->find('.forumline.tablesorter tbody tr') as $item) {
            $url = $item->find('.genmed a.genmed')->attr('href');
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
        $download = $ItemDocument->find('td a.genmed[href^="download"]')->attr('href');
        $item->setFetchUrl(!empty($download) ? (self::SITE_URL . '/' . $download) : '');

        // Page torrent
        $item->setPageUrl($url);

        // Category torrent
        $category = trim($ItemDocument->find('td a.gen[href^="tracker"]')->text());
        $item->setCategory($category);

        // Title torrent
        $item->setTitle(trim($ItemDocument->find('td .topictitle b')->text()));

        // Torrent size
        $item->setSize((float)$ItemDocument->find('td[nowrap] + td.gensmall u')->text());

        // Torrent count seeds
        $item->setSeeds((int)$ItemDocument->find('td.seedmed b')->text());

        // Torrent count peers
        $item->setPeers((int)$ItemDocument->find('td.leechmed b')->text());

        // Date created torrent
        $item->setDate((int)$ItemDocument->find('td.leechmed ~ td.gensmall u')->text());

        yield $item;
    }
}
