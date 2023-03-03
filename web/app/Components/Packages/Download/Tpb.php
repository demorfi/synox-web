<?php declare(strict_types=1);

namespace App\Components\Packages\Download;

use App\Abstracts\Package;
use App\Enums\Category;
use App\Interfaces\Download;
use App\Components\Storage\Journal as JournalStorage;
use App\Interfaces\FilterEnum as FilterEnumInterface;
use App\Package\PackageQuery;
use App\Package\Download\{Item, Torrent};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Traits\Client;
use Generator;
use Exception;

class Tpb extends Package implements Download
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
     * @var string[][]
     */
    protected static array $filters = [
        Category::class => [
            Category::AUDIO->name        => '&cat=100',
            Category::VIDEO->name        => '&cat=200',
            Category::APPLICATIONS->name => '&cat=300',
            Category::GAMES->name        => '&cat=400'
        ]
    ];

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
     */
    public function search(PackageQuery $query): Generator
    {
        $client = new CurlClient;
        $client->useCookie($this->getId());

        $url = $this->urlSearch;
        $query->filter?->each(function (FilterEnumInterface $case) use (&$url) {
            $url .= static::$filters[get_class($case)][$case->name] ?? '';
        });

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
                yield $this->createItem($item);
            } catch (Exception $e) {
                JournalStorage::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * @param array $info
     * @return Item
     */
    protected function createItem(array $info): Item
    {
        $item = new Item($this);

        // Url download torrent
        $item->setFetchUrl($info['info_hash']);

        // Category torrent
        $categories = ['', 'Audio', 'Video', 'Applications', 'Games', 'Porn', 'Other'];
        $item->setCategory($categories[$info['category'][0]] ?? '');

        // Page torrent
        $item->setPageUrl(self::SITE_URL . '/description.php?id=' . $info['id']);

        // Title torrent
        $item->setTitle(trim($info['name']));

        // Torrent size
        $item->setSize((float)$info['size']);

        // Torrent count seeds
        $item->setSeeds((int)$info['seeders']);

        // Torrent count peers
        $item->setPeers((int)$info['leechers']);

        // Date created torrent
        $item->setDate((int)$info['added']);

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $url, Torrent $file): bool
    {
        $client = new CurlClient;
        $client->useCookie($this->getId());

        $content = $this->sendGet($client, sprintf($this->urlFetch, $url));
        if ($file->is($content)) {
            $torrent = $file->decode($content);
            if (!empty($torrent) && isset($torrent['info']['name'])) {
                $file->create($torrent['info']['name'], $content);
            }
        }

        return $file->isAvailable();
    }
}
