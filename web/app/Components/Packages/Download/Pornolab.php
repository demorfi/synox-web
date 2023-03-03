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

class Pornolab extends Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://pornolab.net/forum';

    /**
     * @var string
     */
    protected string $name = 'Pornolab';

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
            Category::VIDEO->name => '&f=1672,1111,508,555,1845,1673,1112,1718,553,1143,1646,1717,1851,1713,512,1712,1775,1450',
            Category::GAMES->name => '&f=1838,1750,1756,1785,1790,1827,1828,1829'
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
        if (!$document->find('.topmenu a[href*="/profile.php?mode=viewprofile"]')->count()) {
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
            return (bool)Helper::document($response)->find('.topmenu a[href*="/profile.php?mode=viewprofile"]')->count();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getTotalPagesFound(Document $document): int
    {
        preg_match('/(?P<result>\d+)\s+\[/i', $document->find('#main_content .maintitle ~ .med')->text(), $matches);
        if (!isset($matches['result']) || !(int)trim($matches['result'])) {
            return 0;
        }

        return (int)($document->find('.bottom_info .nav b + b')->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getNextPage(CurlClient $client, string $url, Document $document): ?Document
    {
        $nextPage = $document->find('.bottom_info .nav a.pg')->last()?->attr('href');
        return !empty($nextPage) ? $this->getPage($client, self::SITE_URL . '/' . $nextPage) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $document): Document
    {
        $href = str_replace(self::SITE_URL, '.', $url);
        $row  = $document->find('.forumline tr.tCenter a.tLink[href="' . $href . '"]')
            ?->closest('tr');
        return Helper::document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItemLinks(Document $document): array
    {
        $links = [];
        foreach ($document->find('.forumline tr.tCenter') as $item) {
            $url = $item->find('a.tLink')->attr('href');
            if (!empty($url)) {
                $links[] = self::SITE_URL . ltrim($url, '.');
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
        $download = $ItemDocument->find('a.tr-dl')->attr('href');
        $item->setFetchUrl(!empty($download) ? (self::SITE_URL . '/' . $download) : '');

        // Page torrent
        $item->setPageUrl($url);

        // Category torrent
        $category = trim($ItemDocument->find('.row1 a.gen')->text());
        $item->setCategory($category);

        // Title torrent
        $item->setTitle(trim($ItemDocument->find('a.tLink')->text()));

        // Torrent size
        $item->setSize((float)$ItemDocument->find('.tLeft + .row1 + .row4 u')->text());

        // Torrent count seeds
        $item->setSeeds((int)$ItemDocument->find('td.seedmed b')->text());

        // Torrent count peers
        $item->setPeers((int)$ItemDocument->find('td.leechmed b')->text());

        // Date created torrent
        $item->setDate((int)$ItemDocument->find('td.row4')->last()?->find('u')->text());

        yield $item;
    }
}
