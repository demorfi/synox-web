<?php declare(strict_types=1);

namespace App\Prototype;

use App\Abstracts\Package;
use App\Components\{Helper, Storage\Journal};
use App\Enums\{ContentType, ItemType};
use App\Interfaces\{PackageContent, Package as PackageInterface};
use App\Package\{Query, Content\Text as TextContent, Item\Text as TextItem};
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Exception;
use Generator;

abstract class Text extends Package implements PackageInterface
{
    use Client;

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
     * @inheritdoc
     */
    public function getItemType(): ItemType
    {
        return ItemType::TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): ContentType
    {
        return ContentType::TEXT;
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
     * @return TextItem
     */
    public function makeItem(): TextItem
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
     * @return string
     */
    protected function buildSearchUrl(Query $query): string
    {
        return str_replace('{query}', urlencode($query->value), $this->urlSearch);
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
     * @inheritdoc
     * @throws PathException
     */
    public function search(Query $query): Generator
    {
        if ($this->hasAuth() && (!$this->hasCredentials() || !$this->isAvailableAccount())) {
            return false;
        }

        $client = new CurlClient;
        $client->useCookie($this->getId());
        $page = $this->getPage($client, $this->buildSearchUrl($query));

        foreach ($this->searchItems($page) as $item) {
            try {
                yield $this->buildItem($item);
            } catch (Exception $e) {
                Journal::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function fetch(string $id, TextContent|PackageContent $content): bool
    {
        if (!$this->hasAuth() || ($this->hasCredentials() && $this->isAvailableAccount())) {
            $client = new CurlClient;
            $client->useCookie($this->getId());
            $content->create(md5($id), $this->searchItemContent($this->getPage($client, $this->buildFetchUrl($id))));
        }

        return $content->isAvailable();
    }

    /**
     * @param Document $page
     * @return Generator
     */
    abstract protected function searchItems(Document $page): Generator;

    /**
     * @param Document $page
     * @return string
     */
    abstract protected function searchItemContent(Document $page): string;

    /**
     * @param string $id
     * @return string
     */
    abstract protected function buildFetchUrl(string $id): string;

    /**
     * @param Element $element
     * @return Generator|TextItem
     */
    abstract protected function buildItem(Element $element): Generator|TextItem;
}