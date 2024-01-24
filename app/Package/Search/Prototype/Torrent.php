<?php declare(strict_types=1);

namespace App\Package\Search\Prototype;

use App\Package\Search\Abstracts\Package;
use App\Package\Search\Enums\Type;
use App\Package\Search\Content\Torrent as TorrentContent;
use App\Package\Search\Item\Torrent as TorrentItem;
use App\Package\Search\Query;
use App\Components\Storage\Journal;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\Document;
use Exception;
use SplObjectStorage;

abstract class Torrent extends Package
{
    /**
     * @var Type
     */
    protected Type $type = Type::TORRENT;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $description;

    /**
     * @var string
     */
    protected string $version = '1.0';

    /**
     * @var Query
     */
    protected readonly Query $query;

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
     * @return TorrentItem
     */
    public function makeItem(): TorrentItem
    {
        return $this->getType()->makeItem($this);
    }

    /**
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
        usleep(500000);
        return $this->document($this->sendGet($client, $url));
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
    public function search(Query $query): iterable
    {
        $this->query = $query;
        if ($this->hasAuth() && (!$this->hasCredentials() || !$this->isAvailableAccount())) {
            return false;
        }

        $storage = new SplObjectStorage();
        $client  = $this->client();
        $client->useCookie($this->getId());
        $page = $this->getPage($client, $this->buildSearchUrl($this->query, $this->numFirstPage));

        // Result is not found
        if (!($countPages = $this->getCountPagesFound($page))) {
            return false;
        }

        $storage->attach($page, iterator_to_array($this->searchItems($page)));

        // Find links on all pages
        if ($countPages > 1) {
            $nextPage = $page;
            for ($i = ($this->numFirstPage + 1); $i <= $countPages; $i++) {
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
                    $itemPage  = $this->getItemPage($client, $url, $rootPage);
                    $itemBuild = $this->buildItem($url, $itemPage, $rootPage);
                    if (is_iterable($itemBuild)) {
                        yield from $itemBuild;
                    } else {
                        yield $itemBuild;
                    }
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
    public function fetch(string $id): ?TorrentContent
    {
        if (!$this->hasAuth() || ($this->hasCredentials() && $this->isAvailableAccount())) {
            $client = $this->client();
            $client->useCookie($this->getId());

            $data = $this->sendGet($client, $this->buildFetchUrl($id));
            $content = $this->getType()->makeContent();
            if ($content->is($data)) {
                $torrent = $content->decode($data);
                if (!empty($torrent) && isset($torrent['info']['piece length'], $torrent['info']['name'])) {
                    $content->create($torrent['info']['name'] . '-' . $torrent['info']['piece length'], $data);
                }
            }
            return $content;
        }

        return null;
    }

    /**
     * @param Document $page
     * @return int
     */
    abstract protected function getCountPagesFound(Document $page): int;

    /**
     * @param Document $page
     * @return iterable
     */
    abstract protected function searchItems(Document $page): iterable;

    /**
     * @param string $id
     * @return string
     */
    abstract protected function buildFetchUrl(string $id): string;

    /**
     * @param string   $url
     * @param Document $itemPage
     * @param Document $rootPage
     * @return iterable|TorrentItem
     */
    abstract protected function buildItem(string $url, Document $itemPage, Document $rootPage): iterable|TorrentItem;
}