<?php declare(strict_types=1);

namespace App\Prototype;

use App\Abstracts\Package;
use App\Components\{Helper, Storage\Journal};
use App\Enums\{ContentType, ItemType};
use App\Interfaces\{PackageContent, Package as PackageInterface};
use App\Package\{Query, Content\Torrent as TorrentContent, Item\Torrent as TorrentItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use Digua\Traits\Client;
use DOMWrap\Document;
use Exception;
use Generator;
use SplObjectStorage;

abstract class Torrent extends Package implements PackageInterface
{
    use Client;

    /**
     * @var Query
     */
    protected readonly Query $query;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $shortDescription;

    /**
     * @var string
     */
    protected string $version = '1.0';

    /**
     * @var string
     */
    protected string $urlSearch;

    /**
     * @var int
     */
    protected int $numFirstPage = 1;

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
     * @return TorrentItem
     */
    public function makeItem(): TorrentItem
    {
        return $this->getItemType()->make($this);
    }

    /**
     * Check available account.
     *
     * @return bool
     */
    protected function isAvailableAccount(): bool
    {
        return !$this->hasAuth();
    }

    /**
     * @return bool
     */
    protected function hasCredentials(): bool
    {
        return !empty($this->getSetting('username')) && !empty($this->getSetting('password'));
    }

    /**
     * @param Query $query
     * @param int   $numPage
     * @return string
     */
    protected function buildSearchUrl(Query $query, int $numPage = 1): string
    {
        return str_replace(['{query}', '{page}'], [urlencode($query->value), $numPage], $this->urlSearch);
    }

    /**
     * @param CurlClient $client
     * @param string     $url
     * @return Document
     */
    protected function getPage(CurlClient $client, string $url): Document
    {
        sleep(1);
        return Helper::document($this->sendGet($client, $url));
    }

    /**
     * @param CurlClient $client
     * @param string     $url
     * @param Document   $page Current page document
     * @return ?Document
     */
    protected function getNextPage(CurlClient $client, string $url, Document $page): ?Document
    {
        return $this->getPage($client, $url);
    }

    /**
     * @param CurlClient $client
     * @param string     $url
     * @param Document   $page
     * @return Document
     */
    protected function getItemPage(CurlClient $client, string $url, Document $page): Document
    {
        return $this->getPage($client, $url);
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function search(Query $query): Generator
    {
        $this->query = $query;
        if ($this->hasAuth() && (!$this->hasCredentials() || !$this->isAvailableAccount())) {
            return false;
        }

        $storage = new SplObjectStorage();
        $client  = new CurlClient;
        $client->useCookie($this->getId());
        $page = $this->getPage($client, $this->buildSearchUrl($this->query, $this->numFirstPage));

        // Result is not found
        if (!($countItems = $this->getCountItemsFound($page))) {
            return false;
        }

        $storage->attach($page, iterator_to_array($this->searchItems($page)));

        // Find links on all pages
        if ($countItems > 1) {
            $nextPage = $page;
            for ($i = ($this->numFirstPage + 1); $i <= $countItems; $i++) {
                try {
                    $nextPage = $this->getNextPage($client, $this->buildSearchUrl($this->query, $i), $nextPage);
                    if (empty($nextPage)) {
                        break;
                    }
                    $storage->attach($nextPage, iterator_to_array($this->searchItems($nextPage)));
                } catch (Exception $e) {
                    Journal::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
                }
            }
        }

        // Loop through all found links
        foreach ($storage as $rootPage) {
            $urls = $storage[$rootPage];
            foreach ($urls as $url) {
                try {
                    $itemPage = $this->getItemPage($client, $url, $rootPage);
                    yield $this->buildItem($url, $itemPage, $rootPage);
                } catch (Exception $e) {
                    Journal::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function fetch(string $id, TorrentContent|PackageContent $content): bool
    {
        if (!$this->hasAuth() || ($this->hasCredentials() && $this->isAvailableAccount())) {
            $client = new CurlClient;
            $client->useCookie($this->getId());

            $data = $this->sendGet($client, $this->buildFetchUrl($id));
            if ($content->is($data)) {
                $torrent = $content->decode($data);
                if (!empty($torrent) && isset($torrent['info']['name'])) {
                    $content->create($torrent['info']['name'], $data);
                }
            }
        }

        return $content->isAvailable();
    }

    /**
     * @param Document $page
     * @return int
     */
    abstract protected function getCountItemsFound(Document $page): int;

    /**
     * @param Document $page
     * @return Generator
     */
    abstract protected function searchItems(Document $page): Generator;

    /**
     * @param string $id
     * @return string
     */
    abstract protected function buildFetchUrl(string $id): string;

    /**
     * @param string   $url
     * @param Document $itemPage
     * @param Document $rootPage
     * @return Generator|TorrentItem
     */
    abstract protected function buildItem(string $url, Document $itemPage, Document $rootPage): Generator|TorrentItem;
}