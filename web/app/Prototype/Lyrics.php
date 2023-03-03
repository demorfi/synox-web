<?php declare(strict_types=1);

namespace App\Prototype;

use App\Abstracts\Package;
use App\Components\Helper;
use App\Components\Storage\Journal as JournalStorage;
use App\Interfaces\Lyrics as LyricsInterface;
use App\Package\Lyrics\{Content, Item};
use App\Package\PackageQuery;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Traits\Client;
use DOMWrap\Document;
use DOMWrap\Element;
use Exception;
use Generator;

abstract class Lyrics extends Package implements LyricsInterface
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
     * @param string $value Search query
     * @return string
     */
    protected function buildSearchUrl(string $value): string
    {
        return sprintf($this->urlSearch, urlencode($value));
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
     */
    public function search(PackageQuery $query): Generator
    {
        if ($this->hasAuth() && (!$this->hasCredentials() || !$this->isAvailableAccount())) {
            return false;
        }

        $client = new CurlClient;
        $client->useCookie($this->getId());
        $document = $this->getPage($client, $this->buildSearchUrl($query->value));

        foreach ($this->searchItemLinks($document) as $item) {
            try {
                yield $this->createItem($item);
            } catch (Exception $e) {
                JournalStorage::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $url, Content $content): bool
    {
        if (!$this->hasAuth() || ($this->hasCredentials() && $this->isAvailableAccount())) {
            $client = new CurlClient;
            $client->useCookie($this->getId());
            $content->add($this->searchItemContent($this->getPage($client, $url)));
        }

        return $content->isAvailable();
    }

    /**
     * @param Document $document
     * @return Generator
     */
    abstract protected function searchItemLinks(Document $document): Generator;

    /**
     * @param Document $document
     * @return string
     */
    abstract protected function searchItemContent(Document $document): string;

    /**
     * @param Element $element
     * @return Item
     */
    abstract protected function createItem(Element $element): Item;
}