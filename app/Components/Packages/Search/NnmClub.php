<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category, Item\Torrent as TorrentItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\Document;

class NnmClub extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'https://nnmclub.to/forum';

    /**
     * @inheritdoc
     */
    protected string $name = 'NNM Club';

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
            [313, 322, 323, 324, 325, 326, 327, 328, 329, 330, 331, 332, 333, 334, 336, 337, 338, 339, 340, 341],
            [344, 345, 346, 347, 348, 349, 352, 353, 354, 358, 359, 360, 361, 363, 364, 365, 366, 367, 368, 369],
            [370, 371, 372, 373, 374, 375, 376, 378, 379, 380, 429, 671, 672, 673, 674, 680, 681, 711, 824, 876],
            [877, 879, 917, 961, 962, 963, 965, 976, 977, 979, 980, 981, 982, 983, 984, 1149, 1157, 1158, 1159, 1160],
            [1161, 1162, 1163, 1164, 1165, 1166, 1167, 1168, 1178, 1179, 1180, 1181, 1182, 1183, 1184, 1185, 1186, 1187, 1188, 1189],
            [1190, 1213, 1215, 1216, 1217, 1218, 1224, 1225, 1234, 1243, 1255, 1256, 1257, 1258, 1259, 1260, 1261, 1285, 1291, 1316],
            [1317, 1323, 1324, 1325, 1326, 1327, 1328]
        ],

        Category::VIDEO->value => [
            [216, 217, 218, 219, 220, 221, 222, 224, 225, 226, 227, 228, 254, 255, 256, 257, 258, 264, 265, 266],
            [270, 271, 272, 318, 319, 320, 321, 576, 577, 578, 579, 580, 581, 582, 584, 585, 586, 587, 588, 589],
            [590, 591, 593, 594, 595, 596, 597, 598, 599, 600, 603, 604, 605, 606, 607, 608, 609, 610, 611, 612],
            [613, 614, 615, 616, 617, 619, 620, 621, 622, 623, 624, 625, 626, 627, 628, 632, 634, 635, 638, 639],
            [640, 644, 645, 646, 648, 652, 653, 654, 656, 677, 678, 682, 693, 694, 706, 713, 714, 722, 750, 761],
            [768, 769, 770, 771, 772, 773, 774, 775, 776, 777, 779, 780, 781, 782, 783, 784, 785, 786, 787, 791],
            [793, 794, 795, 796, 799, 800, 804, 806, 809, 812, 819, 882, 883, 884, 885, 889, 891, 894, 905, 908],
            [909, 910, 911, 912, 913, 922, 924, 950, 951, 953, 954, 955, 956, 959, 974, 975, 1062, 1140, 1141, 1142],
            [1144, 1150, 1177, 1194, 1206, 1210, 1211, 1219, 1220, 1221, 1242, 1262, 1263, 1265, 1288, 1290, 1293, 1294, 1295, 1296],
            [1298, 1299, 1300, 1308, 1309, 1310, 1311, 1312, 1313, 1320, 1322]
        ],

        Category::APPLICATION->value => [
            [166, 267, 417, 503, 504, 506, 508, 509, 510, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 521],
            [522, 523, 524, 525, 526, 527, 529, 530, 532, 533, 534, 535, 536, 537, 538, 545, 548, 549, 550, 552],
            [553, 554, 561, 562, 563, 564, 676, 717, 763, 764, 765, 808, 820, 822, 828, 829, 830, 831, 832, 833],
            [834, 839, 840, 841, 842, 843, 844, 916, 988, 1023, 1025, 1026, 1028, 1029, 1030, 1031, 1032, 1034, 1035, 1036],
            [1037, 1038, 1039, 1042, 1070, 1071, 1072, 1073, 1074, 1075, 1076, 1077, 1078, 1082, 1083, 1087, 1091, 1092, 1093, 1095],
            [1096, 1097, 1098, 1099, 1102, 1103, 1105, 1107, 1111, 1113, 1114, 1115, 1116, 1129, 1134, 1137, 1139, 1151, 1155, 1156],
            [1192, 1193, 1231, 1232, 1233, 1236, 1238, 1240, 1241, 1254, 1266, 1267, 1268, 1269, 1270, 1271, 1272, 1273, 1274, 1275],
            [1276, 1277, 1284, 1335]
        ],

        Category::GAME->value => [
            [268, 382, 383, 384, 385, 386, 387, 388, 389, 390, 391, 410, 411, 412, 413, 414, 415, 416, 418, 428],
            [746, 848, 968, 969, 970, 971, 972, 1008, 1009, 1010, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1041, 1044, 1045],
            [1046, 1047, 1048, 1049, 1050, 1051, 1052, 1053, 1054, 1056, 1057, 1058, 1059, 1060, 1061, 1146, 1264, 1292, 1318, 1321]
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

        $document = $this->document($this->sendGet($client, self::SITE_URL . '/tracker.php'));
        if (!$document->find('a[href^="login.php?logout"]')->count()) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username' => $this->getSetting('username'),
                    'password' => $this->getSetting('password'),
                    'autologin' => '1',
                    'login' => ''
                ]
            );
            return (bool)$this->document($response)->find('a[href^="login.php?logout"]')->count();
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
        preg_match('/(?P<result>\d+)\s+\(max/i', $page->find('#search_form table .nav')->text(), $matches);
        if (!isset($matches['result']) || !(int)trim($matches['result'])) {
            return 0;
        }

        return (int)($page->find('#search_form table td span b + b')->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function getNextPage(CurlClient $client, string $url, Document $page): ?Document
    {
        $nextPage = $page->find('#search_form table .nav b + a')->attr('href');
        return !empty($nextPage) ? $this->getPage($client, self::SITE_URL . '/' . $nextPage) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemPage(CurlClient $client, string $url, Document $page): Document
    {
        $href = str_replace(self::SITE_URL . '/', '', $url);
        $row  = $page->find('.forumline.tablesorter tbody tr .genmed a.genmed[href="' . $href . '"]')
            ?->closest('tr');
        return $this->document($row->getOuterHtml());
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): iterable
    {
        foreach ($page->find('.forumline.tablesorter tbody tr') as $item) {
            $url = $item->find('.genmed a.genmed')->attr('href');
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

        $item->setFetchId($itemPage->find('td a.genmed[href^="download"]')->attr('href'));
        $item->setPageUrl($url);

        // Category torrent
        preg_match('/\?f=(?P<id>\d+)/i', $itemPage->find('td a.gen[href^="tracker"]')->attr('href'), $matches);
        if (isset($matches['id']) && ($categoryId = (int)$matches['id']) >= 1) {
            if (($categoryName = Category::tryFromArray($categoryId, self::$categories)) !== null) {
                $item->setCategory($categoryName);
            }
        }

        // Category torrent
        $propCategory = trim($itemPage->find('td a.gen[href^="tracker"]')->text());
        if (!empty($propCategory)) {
            $item->addProperty('Tracker category', $propCategory);
        }

        $item->setTitle(trim($itemPage->find('td .topictitle b')->text()));
        $item->setSize((float)$itemPage->find('td[nowrap] + td.gensmall u')->text());
        $item->setSeeds((int)$itemPage->find('td.seedmed b')->text());
        $item->setPeers((int)$itemPage->find('td.leechmed b')->text());
        $item->setDate((int)$itemPage->find('td.leechmed ~ td.gensmall u')->text());

        return $item;
    }
}