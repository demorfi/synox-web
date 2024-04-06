<?php declare(strict_types=1);

namespace App\Components\Packages;

use App\Components\{Storage\Journal};
use App\Package\Search\Abstracts\Package;
use App\Package\Search\Content\Torrent as TorrentContent;
use App\Package\Search\Enums\{Category, Subtype};
use App\Package\Search\Filter;
use App\Package\Search\Item\Torrent as TorrentItem;
use App\Package\Search\Query;
use App\Package\Settings;
use Digua\Exceptions\Path as PathException;
use Exception;

class Jackett extends Package
{
    /**
     * @var Subtype
     */
    private Subtype $subtype = Subtype::TORRENT;

    /**
     * @var string
     */
    private string $name = 'Jackett';

    /**
     * @var string
     */
    private string $description = 'Jackett Results Indexer';

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @var string
     */
    private string $queryFormat = '%s?apikey=%s&query=%s';

    /**
     * @var string
     */
    private string $urlFetch = 'https://itorrents.org/torrent/%s.torrent';

    /**
     * @var array|int[][]
     */
    private static array $categories = [
        Category::VIDEO->value => [2000, 5000]
    ];

    /**
     * @param Settings $settings
     */
    public function __construct(private readonly Settings $settings)
    {
        parent::__construct($this->settings);
        $this->addSetting('text', 'api-url', '', 'Jackett API Results URL');
        $this->addSetting('text', 'api-key', '', 'Jackett API Key');
    }

    /**
     * @inheritdoc
     */
    public function getSubtype(): Subtype
    {
        return $this->subtype;
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
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !empty($this->getSetting('api-url'));
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function search(Query $query): iterable
    {
        $client = $this->client();
        $client->useCookie($this->getId());

        $url = $this->getSetting('api-url');
        $key = $this->getSetting('api-key', 0);

        $response = $this->sendGet($client, sprintf($this->queryFormat, $url, $key, urlencode($query->value)));
        if (empty($response)) {
            return false;
        }

        $data = @json_decode($response, flags: JSON_OBJECT_AS_ARRAY);
        if (empty($data) || !isset($data['Results'][0]['Title'])) {
            return false;
        }

        foreach ($data['Results'] as $item) {
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

        $item->addProperty('Trackers', $info['Tracker']);
        $item->setTitle(trim($info['Title']));
        if (isset($info['Details'])) {
            $item->setPageUrl($info['Details']);
        }

        $item->setSize((float)$info['Size']);
        $item->setSeeds((int)$info['Seeders']);
        $item->setPeers((int)$info['Peers']);

        if (isset($info['MagnetUri'])) {
            $content = $this->subtype->makeContent();
            $content->setMagnet($info['MagnetUri']);
            $item->setContent($content);
            $item->setFetchId($content->getHash());
        }

        $item->setDate((int)strtotime($info['PublishDate'] ?: '0'));

        // Category torrent
        if (isset($info['Category'][0])) {
            if (($categoryName = Category::tryFromArray($info['Category'][0], self::$categories)) !== null) {
                $item->setCategory($categoryName);
            }
        }

        if (isset($info['CategoryDesc'])) {
            $item->addProperty('Tracker category', $info['CategoryDesc']);
        }
        return $item;
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function fetch(Query $query): TorrentContent
    {
        $client = $this->client();
        $client->useCookie($this->getId());

        $content = $this->sendGet($client, sprintf($this->urlFetch, $query->value));
        return $this->subtype->makeContent()->create('', $content);
    }
}