<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category, Item\Torrent as TorrentItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\Document;
use Generator;

class RuTracker extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'https://rutracker.org/forum';

    /**
     * @inheritdoc
     */
    protected string $name = 'Rutracker';

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
        Category::AUDIO->value => [
            [123, 172, 202, 236, 239, 282, 399, 400, 401, 402, 403, 406, 408, 409, 413, 416, 424, 425, 428, 429],
            [431, 436, 441, 445, 446, 450, 453, 454, 460, 463, 464, 465, 466, 467, 468, 469, 475, 490, 499, 506],
            [518, 530, 556, 557, 558, 560, 574, 655, 661, 691, 695, 702, 714, 715, 716, 722, 731, 735, 737, 738],
            [739, 740, 783, 784, 785, 786, 793, 794, 796, 797, 798, 840, 855, 860, 880, 909, 951, 952, 969, 974],
            [983, 984, 986, 988, 1036, 1107, 1121, 1122, 1125, 1126, 1127, 1128, 1129, 1130, 1131, 1132, 1133, 1134, 1135, 1136],
            [1137, 1138, 1141, 1142, 1163, 1164, 1170, 1172, 1173, 1189, 1215, 1216, 1217, 1219, 1220, 1221, 1223, 1224, 1225, 1226],
            [1227, 1228, 1279, 1282, 1283, 1284, 1285, 1299, 1330, 1331, 1334, 1350, 1351, 1361, 1362, 1388, 1395, 1396, 1397, 1444],
            [1452, 1486, 1499, 1625, 1631, 1634, 1635, 1648, 1660, 1665, 1698, 1702, 1703, 1704, 1705, 1706, 1707, 1708, 1709, 1710],
            [1711, 1712, 1713, 1714, 1715, 1716, 1719, 1720, 1724, 1725, 1726, 1727, 1728, 1729, 1730, 1731, 1732, 1736, 1737, 1738],
            [1739, 1740, 1741, 1742, 1743, 1744, 1745, 1746, 1747, 1748, 1749, 1753, 1754, 1755, 1756, 1757, 1758, 1759, 1760, 1764],
            [1765, 1766, 1767, 1768, 1769, 1770, 1771, 1772, 1773, 1774, 1775, 1777, 1778, 1779, 1780, 1781, 1782, 1783, 1784, 1787],
            [1788, 1789, 1790, 1791, 1792, 1793, 1795, 1796, 1797, 1799, 1805, 1807, 1808, 1809, 1810, 1811, 1812, 1815, 1816, 1818],
            [1819, 1821, 1822, 1824, 1825, 1826, 1827, 1828, 1829, 1830, 1831, 1832, 1833, 1834, 1835, 1836, 1837, 1838, 1839, 1840],
            [1841, 1842, 1844, 1847, 1849, 1852, 1856, 1857, 1858, 1859, 1860, 1861, 1862, 1864, 1865, 1866, 1867, 1868, 1869, 1871],
            [1873, 1875, 1877, 1878, 1880, 1881, 1884, 1885, 1886, 1887, 1890, 1892, 1893, 1894, 1895, 1907, 1912, 1913, 1944, 1945],
            [1946, 1947, 1990, 2018, 2084, 2085, 2088, 2127, 2137, 2152, 2165, 2174, 2175, 2219, 2229, 2230, 2231, 2232, 2233, 2261],
            [2262, 2263, 2264, 2266, 2267, 2268, 2269, 2270, 2271, 2275, 2277, 2278, 2279, 2280, 2281, 2282, 2283, 2284, 2285, 2286],
            [2287, 2288, 2289, 2290, 2292, 2293, 2295, 2296, 2297, 2298, 2301, 2302, 2303, 2304, 2305, 2306, 2307, 2308, 2309, 2310],
            [2311, 2324, 2325, 2326, 2327, 2328, 2329, 2330, 2331, 2342, 2348, 2351, 2352, 2353, 2377, 2378, 2379, 2383, 2384, 2387],
            [2388, 2389, 2400, 2401, 2403, 2426, 2430, 2431, 2495, 2497, 2499, 2500, 2501, 2502, 2503, 2504, 2505, 2507, 2508, 2509],
            [2511, 2512, 2513, 2529, 2530, 2531]
        ],

        Category::VIDEO->value => [
            [4, 7, 9, 19, 22, 24, 33, 46, 56, 79, 80, 81, 84, 85, 91, 93, 97, 98, 100, 101],
            [103, 104, 106, 110, 113, 114, 115, 119, 121, 124, 126, 136, 137, 140, 166, 173, 175, 181, 184, 185],
            [187, 188, 189, 193, 194, 195, 208, 209, 212, 235, 242, 249, 251, 252, 255, 256, 257, 259, 260, 262],
            [263, 265, 266, 271, 272, 273, 283, 294, 312, 313, 314, 325, 343, 352, 372, 373, 376, 387, 393, 404],
            [484, 486, 489, 497, 498, 500, 504, 505, 507, 511, 514, 521, 528, 532, 534, 536, 539, 549, 550, 552],
            [572, 592, 594, 599, 607, 610, 614, 615, 617, 625, 626, 656, 660, 670, 671, 672, 677, 694, 704, 709],
            [717, 718, 721, 751, 752, 775, 781, 809, 815, 816, 819, 820, 821, 822, 825, 827, 842, 845, 851, 854],
            [875, 876, 877, 882, 893, 905, 911, 915, 920, 921, 930, 934, 939, 941, 978, 979, 990, 1102, 1105, 1106],
            [1114, 1120, 1144, 1171, 1186, 1188, 1213, 1214, 1229, 1235, 1242, 1247, 1248, 1254, 1255, 1257, 1258, 1259, 1260, 1261],
            [1278, 1280, 1281, 1287, 1288, 1301, 1311, 1315, 1319, 1323, 1326, 1327, 1332, 1336, 1339, 1343, 1359, 1386, 1387, 1389],
            [1390, 1391, 1392, 1408, 1417, 1434, 1442, 1449, 1453, 1457, 1459, 1460, 1463, 1467, 1468, 1469, 1470, 1472, 1475, 1479],
            [1481, 1482, 1484, 1485, 1491, 1493, 1495, 1498, 1527, 1531, 1535, 1537, 1539, 1542, 1543, 1544, 1545, 1546, 1547, 1548],
            [1549, 1550, 1551, 1552, 1553, 1554, 1555, 1556, 1559, 1560, 1561, 1562, 1563, 1564, 1565, 1566, 1567, 1568, 1569, 1570],
            [1573, 1574, 1576, 1577, 1581, 1583, 1585, 1586, 1587, 1588, 1590, 1591, 1592, 1593, 1594, 1595, 1596, 1597, 1608, 1613],
            [1614, 1615, 1616, 1617, 1620, 1621, 1623, 1626, 1630, 1642, 1653, 1654, 1655, 1656, 1666, 1667, 1668, 1669, 1670, 1675],
            [1690, 1693, 1697, 1794, 1803, 1900, 1929, 1930, 1931, 1932, 1939, 1940, 1949, 1950, 1952, 1959, 1986, 1987, 1991, 1997],
            [1998, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2014, 2016, 2017, 2065, 2068, 2069, 2073, 2075, 2076],
            [2078, 2079, 2089, 2090, 2091, 2092, 2093, 2097, 2100, 2102, 2103, 2107, 2109, 2110, 2111, 2112, 2113, 2123, 2124, 2135],
            [2136, 2138, 2139, 2140, 2159, 2160, 2163, 2164, 2166, 2168, 2169, 2171, 2176, 2177, 2178, 2198, 2199, 2200, 2201, 2208],
            [2209, 2210, 2211, 2220, 2221, 2258, 2294, 2323, 2335, 2338, 2339, 2343, 2350, 2365, 2366, 2370, 2380, 2393, 2396, 2398],
            [2404, 2405, 2406, 2412, 2425, 2455, 2459, 2475, 2479, 2482, 2484, 2485, 2486, 2491, 2493, 2514, 2522, 2532, 2533, 2537],
            [2538, 2540, 2544]
        ],

        Category::APPLICATION->value => [
            [285, 286, 287, 288, 289, 290, 291, 292, 315, 633, 828, 829, 830, 831, 835, 839, 863, 864, 890, 957],
            [1003, 1005, 1009, 1010, 1011, 1012, 1013, 1014, 1016, 1018, 1019, 1021, 1025, 1027, 1028, 1029, 1030, 1031, 1032, 1033],
            [1034, 1035, 1038, 1039, 1040, 1041, 1042, 1051, 1052, 1053, 1054, 1055, 1056, 1057, 1058, 1059, 1060, 1061, 1062, 1063],
            [1064, 1065, 1066, 1067, 1068, 1071, 1073, 1079, 1080, 1081, 1082, 1083, 1084, 1085, 1086, 1087, 1088, 1089, 1090, 1091],
            [1092, 1192, 1193, 1195, 1199, 1204, 1290, 1357, 1363, 1366, 1368, 1370, 1371, 1372, 1373, 1374, 1375, 1376, 1379, 1381],
            [1383, 1394, 1473, 1503, 1507, 1508, 1509, 1510, 1511, 1512, 1513, 1514, 1515, 1516, 1517, 1526, 1536, 1636, 1674, 1679],
            [1908, 1909, 1927, 1933, 1935, 1936, 1937, 1954, 1962, 1963, 2077, 2082, 2134, 2153, 2154, 2235, 2236, 2237, 2238, 2240],
            [2241, 2244, 2248, 2421, 2489, 2492, 2523, 2534, 2535]
        ],

        Category::GAME->value => [
            [5, 50, 51, 52, 53, 54, 127, 128, 129, 139, 240, 278, 357, 510, 537, 546, 548, 595, 635, 637],
            [646, 647, 650, 700, 773, 774, 886, 887, 899, 900, 908, 960, 968, 973, 1001, 1002, 1004, 1008, 1116, 1352],
            [1605, 1926, 1992, 2012, 2059, 2060, 2118, 2142, 2143, 2145, 2146, 2149, 2179, 2180, 2181, 2182, 2185, 2186, 2203, 2204],
            [2226, 2228, 2415, 2420, 2478, 2480, 2481, 2487]
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
     * @throws PathException
     */
    protected function isAvailableAccount(): bool
    {
        $client = $this->client();
        $client->useCookie($this->getId());

        $document = $this->document($this->sendGet($client, self::SITE_URL . '/index.php'));
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
            return (bool)$this->document($response)->find('.logged-in-as-uname')->count();
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
            '/(?P<result>\d+)\s+\(/',
            $page->find('#main_content_wrap > table td p + p')->text(),
            $matches
        );

        if (!isset($matches['result']) || !(int)trim($matches['result'])) {
            return 0;
        }

        return (int)($page->find('.bottom_info p b + b')->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getNextPage(CurlClient $client, string $url, Document $page): ?Document
    {
        $nextPage = $page->find('#main_content_wrap > table h1 + p a')->last()?->attr('href');
        return !empty($nextPage) ? $this->getPage($client, self::SITE_URL . '/' . $nextPage) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $page): Document
    {
        $href = str_replace(self::SITE_URL . '/', '', $url);
        $row  = $page->find('.forumline tr.hl-tr a.tLink[href="' . $href . '"]')
            ?->closest('tr');
        return $this->document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): Generator
    {
        foreach ($page->find('.forumline tr.hl-tr') as $item) {
            $url = $item->find('a.tLink')->attr('href');
            if (!empty($url)) {
                yield self::SITE_URL . '/' . $url;
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

        $item->setFetchId($itemPage->find('td.tor-size a.tr-dl')->attr('href'));
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/\?f=(?P<id>\d+)/i', $itemPage->find('.f-name a.ts-text')->attr('href'), $matches);
        if (isset($matches['id']) && ($categoryId = (int)$matches['id']) >= 1) {
            if (($categoryName = Category::tryFromArray($categoryId, self::$categories)) !== null) {
                $item->setCategory($categoryName);
            }
        }

        $propCategory = trim($itemPage->find('.f-name a.ts-text')->text());
        if (!empty($propCategory)) {
            $item->addProperty('Tracker category', $propCategory);
        }

        $item->setTitle(trim($itemPage->find('.t-title a.tLink')->text()));
        $item->setSize((float)$itemPage->find('td.tor-size')->attr('data-ts_text'));
        $item->setSeeds((int)$itemPage->find('td .seedmed')->text());
        $item->setPeers((int)$itemPage->find('td.leechmed')->text());
        $item->setDate((int)$itemPage->find('td')->last()?->attr('data-ts_text'));

        return $item;
    }
}