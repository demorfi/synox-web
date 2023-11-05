<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Abstracts\Package;
use App\Components\Storage\Journal;
use App\Enums\{Category, ContentType, ItemType};
use App\Package\{Query, Content\Torrent as TorrentContent, Item\Torrent as TorrentItem};
use App\Interfaces\PackageContent;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Components\ArrayCollection;
use Digua\Exceptions\Path as PathException;
use Digua\Traits\Client;
use Exception;
use Generator;

class Tpb extends Package
{
    use Client;

    /**
     * @var string
     */
    const SITE_URL = 'https://thepiratebay.org';

    /**
     * @var string
     */
    protected string $name = 'The Pirate Bay';

    /**
     * @var string
     */
    protected string $shortDescription = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @var string
     */
    protected string $version = '1.0';

    /**
     * @var string
     */
    protected string $urlSearch = 'https://apibay.org/q.php?q=%s';

    /**
     * @var string
     */
    protected string $urlFetch = 'https://itorrents.org/torrent/%s.torrent';

    /**
     * @var array|int[][]
     */
    protected static array $categories = [
        Category::AUDIO->value        => [100],
        Category::VIDEO->value        => [200, 500],
        Category::APPLICATIONS->value => [300],
        Category::GAMES->value        => [400]
    ];

    /**
     * @inheritdoc
     */
    public function getItemType(): ItemType
    {
        return ItemType::TORRENT;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): ContentType
    {
        return ContentType::TORRENT;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function hasAuth(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function search(Query $query): Generator
    {
        $client = new CurlClient;
        $client->useCookie($this->getId());

        $url      = $this->urlSearch;
        $response = $this->sendGet($client, sprintf($url, urlencode($query->value)));
        if (empty($response)) {
            return false;
        }

        $data = @json_decode($response, flags: JSON_OBJECT_AS_ARRAY);
        if (empty($data) || (isset($data[0]['id']) && !(int)$data[0]['id'])) {
            return false;
        }

        foreach ($data as $item) {
            try {
                yield $this->buildItem($item);
            } catch (Exception $e) {
                Journal::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * @param array $info
     * @return TorrentItem
     */
    protected function buildItem(array $info): TorrentItem
    {
        $item = new TorrentItem($this);

        // Url download torrent
        $item->setFetchId($info['info_hash']);
        $item->setPageUrl(self::SITE_URL . '/description.php?id=' . $info['id']);

        // Category torrent
        $categories    = ['', 'Audio', 'Video', 'Applications', 'Games', 'Porn', 'Other'];
        $categoryIdKey = $info['category'][0];
        $categoryName  = ArrayCollection::make(self::$categories)->search($categoryIdKey . '00', recursive: true)->firstKey();
        if (!empty($categoryName) && ($categoryName = Category::tryFrom($categoryName)) !== null) {
            $item->setCategory($categoryName);
        }

        $propCategory = $categories[$categoryIdKey] ?? '';
        if (!empty($propCategory)) {
            $item->addProperty('Tracker category', $propCategory);
        }

        $item->setTitle(trim($info['name']));
        $item->setSize((float)$info['size']);
        $item->setSeeds((int)$info['seeders']);
        $item->setPeers((int)$info['leechers']);
        $item->setDate((int)$info['added']);

        return $item;
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function fetch(string $id, TorrentContent|PackageContent $content): bool
    {
        $client = new CurlClient;
        $client->useCookie($this->getId());

        $data = $this->sendGet($client, sprintf($this->urlFetch, $id));
        if ($content->is($data)) {
            $torrent = $content->decode($data);
            if (!empty($torrent) && isset($torrent['info']['name'])) {
                $content->create($torrent['info']['name'], $data);
            }
        }

        return $content->isAvailable();
    }
}
