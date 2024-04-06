<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category, Item\Torrent as TorrentItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\Document;

class Pornolab extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'https://pornolab.net/forum';

    /**
     * @inheritdoc
     */
    protected string $name = 'Pornolab';

    /**
     * @inheritdoc
     */
    protected string $description = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/tracker.php?nm={query}';

    /**
     * @var string
     */
    protected string $urlLogin = self::SITE_URL . '/login.php';

    /**
     * @var array|int[][]
     */
    protected static array $categories = [
        Category::VIDEO->value => [
            [36, 60, 284, 508, 509, 512, 553, 555, 902, 903, 997, 1110, 1111, 1112, 1124, 1143, 1450, 1451, 1644, 1646],
            [1670, 1671, 1672, 1673, 1674, 1675, 1676, 1677, 1678, 1679, 1680, 1681, 1682, 1685, 1688, 1691, 1707, 1711, 1712, 1713],
            [1715, 1717, 1718, 1719, 1733, 1734, 1740, 1741, 1745, 1752, 1754, 1755, 1758, 1760, 1762, 1763, 1765, 1767, 1768, 1769],
            [1775, 1777, 1780, 1781, 1784, 1787, 1788, 1789, 1791, 1792, 1793, 1797, 1798, 1800, 1801, 1803, 1804, 1805, 1818, 1819],
            [1820, 1823, 1825, 1826, 1830, 1831, 1834, 1836, 1837, 1842, 1843, 1845, 1846, 1847, 1849, 1851, 1853, 1856, 1857, 1859],
            [1861, 1862]
        ],

        Category::GAME->value => [1750, 1756, 1785, 1790, 1827, 1828, 1838]
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

        $document = $this->document($this->sendGet($client, self::SITE_URL . '/tracker.php'));
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
            return (bool)$this->document($response)->find('.topmenu a[href*="/profile.php?mode=viewprofile"]')->count();
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
        preg_match('/(?P<result>\d+)\s+\[/i', $page->find('#main_content .maintitle ~ .med')->text(), $matches);
        if (!isset($matches['result']) || !(int)trim($matches['result'])) {
            return 0;
        }

        return (int)($page->find('.bottom_info .nav b + b')->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getNextPage(CurlClient $client, string $url, Document $page): ?Document
    {
        $nextPage = $page->find('.bottom_info .nav a.pg')->last()?->attr('href');
        return !empty($nextPage) ? $this->getPage($client, self::SITE_URL . '/' . $nextPage) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $page): Document
    {
        $href = str_replace(self::SITE_URL, '.', $url);
        $row  = $page->find('.forumline tr.tCenter a.tLink[href="' . $href . '"]')?->closest('tr');
        return $this->document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): iterable
    {
        foreach ($page->find('.forumline tr.tCenter') as $item) {
            $url = $item->find('a.tLink')->attr('href');
            if (!empty($url)) {
                yield self::SITE_URL . ltrim($url, '.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function buildFetchUrl(string $id): string
    {
        return self::SITE_URL . '/' . $id;
    }

    /**
     * @inheritdoc
     */
    protected function buildItem(string $url, Document $itemPage, Document $rootPage): TorrentItem
    {
        $item = $this->makeItem();

        $item->setFetchId($itemPage->find('a.tr-dl')->attr('href'));
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/\?f=(?P<id>\d+)/i', $itemPage->find('.row1 a.gen')->attr('href'), $matches);
        if (isset($matches['id']) && ($categoryId = (int)$matches['id']) >= 1) {
            if (($categoryName = Category::tryFromArray($categoryId, self::$categories)) !== null) {
                $item->setCategory($categoryName);
            }
        }

        $propCategory = trim($itemPage->find('.row1 a.gen')->text());
        if (!empty($propCategory)) {
            $item->addProperty('Tracker category', $propCategory);
        }

        $item->setTitle(trim($itemPage->find('a.tLink')->text()));
        $item->setSize((float)$itemPage->find('.tLeft + .row1 + .row4 u')->text());
        $item->setSeeds((int)$itemPage->find('td.seedmed b')->text());
        $item->setPeers((int)$itemPage->find('td.leechmed b')->text());
        $item->setDate((int)$itemPage->find('td.row4')->last()?->find('u')->text());

        return $item;
    }
}