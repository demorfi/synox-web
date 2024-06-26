<?php declare(strict_types=1);

namespace App\Package\Search\Prototype;

use App\Package\Search\Abstracts\Package;
use App\Package\Search\Enums\Subtype;
use App\Package\Search\Content\Text as TextContent;
use App\Package\Search\Item\Text as TextItem;
use App\Package\Search\Query;
use App\Components\Storage\Journal;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Exceptions\Path as PathException;
use DOMWrap\{Document, Element};
use Exception;

abstract class Text extends Package
{
    /**
     * @var Subtype
     */
    protected Subtype $subtype = Subtype::TEXT;

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
     * @var string
     */
    protected string $urlSearch;

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
     * @return TextItem
     */
    public function makeItem(): TextItem
    {
        return $this->subtype->makeItem($this);
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
        usleep(500000);
        return $this->document($this->sendGet($client, $url));
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function search(Query $query): iterable
    {
        if ($this->hasAuth() && (!$this->hasCredentials() || !$this->isAvailableAccount())) {
            return false;
        }

        $client = $this->client();
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
    public function fetch(Query $query): ?TextContent
    {
        if (!$this->hasAuth() || ($this->hasCredentials() && $this->isAvailableAccount())) {
            $client = $this->client();
            $client->useCookie($this->getId());
            return $this->subtype->makeContent()
                ->create(md5($query->value), $this->searchItemContent($this->getPage($client, $this->buildFetchUrl($query->value))));
        }

        return null;
    }

    /**
     * @param Document $page
     * @return iterable
     */
    abstract protected function searchItems(Document $page): iterable;

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
     * @return iterable|TextItem
     */
    abstract protected function buildItem(Element $element): iterable|TextItem;
}