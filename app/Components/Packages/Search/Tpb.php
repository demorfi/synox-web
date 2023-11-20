<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\Abstracts\Package;
use App\Package\Search\Filter;
use App\Package\Search\Enums\{Type, Category};
use App\Package\Search\Query;
use App\Package\Search\Item\Torrent as TorrentItem;
use App\Package\Search\Content\Torrent as TorrentContent;
use App\Components\Storage\Journal;
use Digua\Exceptions\Path as PathException;
use Exception;
use Generator;

class Tpb extends Package
{
    /**
     * @var string
     */
    const SITE_URL = 'https://thepiratebay.org';

    /**
     * @var Type
     */
    private Type $type = Type::TORRENT;

    /**
     * @var string
     */
    private string $name = 'The Pirate Bay';

    /**
     * @var string
     */
    private string $description = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @var string
     */
    private string $urlSearch = 'https://apibay.org/q.php?q=%s';

    /**
     * @var string
     */
    private string $urlFetch = 'https://itorrents.org/torrent/%s.torrent';

    /**
     * @var array|int[][]
     */
    private static array $categories = [
        Category::AUDIO->value        => [100],
        Category::VIDEO->value        => [200, 500],
        Category::APPLICATION->value => [300],
        Category::GAME->value        => [400]
    ];

    /**
     * @inheritdoc
     */
    public function getType(): Type
    {
        return $this->type;
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
    public function getDescription(): string
    {
        return $this->description;
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
     */
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => array_keys(self::$categories)]);
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function search(Query $query): Generator
    {
        $client = $this->client();
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
        if (($categoryName = Category::tryFromArray($categoryIdKey . '00', self::$categories)) !== null) {
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
    public function fetch(string $id): TorrentContent
    {
        $client = $this->client();
        $client->useCookie($this->getId());

        $data = $this->sendGet($client, sprintf($this->urlFetch, $id));
        $content = $this->getType()->makeContent();
        if ($content->is($data)) {
            $torrent = $content->decode($data);
            if (!empty($torrent) && isset($torrent['info']['name'])) {
                $content->create($torrent['info']['name'], $data);
            }
        }

        return $content;
    }
}