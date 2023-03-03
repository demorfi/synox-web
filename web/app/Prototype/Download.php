<?php declare(strict_types=1);

namespace App\Prototype;

use App\Abstracts\Package;
use App\Components\Helper;
use App\Components\Storage\Journal as JournalStorage;
use App\Enums\Category;
use App\Interfaces\{
    Download as DownloadInterface,
    FilterEnum as FilterEnumInterface
};
use App\Package\Download\Torrent;
use App\Package\PackageQuery;
use Digua\Components\Client\Curl as CurlClient;
use Digua\Traits\Client;
use DOMWrap\Document;
use Exception;
use Generator;
use SplObjectStorage;

abstract class Download extends Package implements DownloadInterface
{
    use Client;

    /**
     * @var PackageQuery
     */
    protected readonly PackageQuery $query;

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
    protected int $firstPage = 1;

    /**
     * @var string[][]
     */
    protected static array $filters = [
        Category::class => [
            // Append to search url
            Category::AUDIO->name        => '', // string or array ['search url regexp', 'replace']
            Category::VIDEO->name        => '',
            Category::APPLICATIONS->name => '',
            Category::GAMES->name        => ''
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
     * @param string $url
     * @return string
     */
    protected function buildFiltersUrl(string $url): string
    {
        $this->query->filter?->each(function (FilterEnumInterface $case) use (&$url) {
            $filter = static::$filters[get_class($case)][$case->name] ?? '';
            if (is_array($filter) && sizeof($filter) === 2) {
                [$search, $replace] = $filter;
                $url = preg_replace('~' . $search . '~', (string)$replace, $url, 1);
                return;
            }

            $url .= $filter;
        });

        return $url;
    }

    /**
     * @param string $name Search query
     * @param int    $page Page number
     * @return string
     */
    protected function buildSearchUrl(string $name, int $page = 1): string
    {
        return sprintf($this->buildFiltersUrl($this->urlSearch), urlencode($name), $page);
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
     * @param Document   $document Current page document
     * @return ?Document
     */
    protected function getNextPage(CurlClient $client, string $url, Document $document): ?Document
    {
        return $this->getPage($client, $url);
    }

    /**
     * @param CurlClient $client
     * @param string     $url
     * @param Document   $document
     * @return Document
     */
    protected function getItemPage(CurlClient $client, string $url, Document $document): Document
    {
        return $this->getPage($client, $url);
    }

    /**
     * @inheritdoc
     */
    public function search(PackageQuery $query): Generator
    {
        $this->query = $query;
        if ($this->hasAuth() && (!$this->hasCredentials() || !$this->isAvailableAccount())) {
            return false;
        }

        $client = new CurlClient;
        $client->useCookie($this->getId());
        $document   = $this->getPage($client, $this->buildSearchUrl($query->value, $this->firstPage));
        $totalPages = $this->getTotalPagesFound($document);

        // Result is not found
        if (!$totalPages) {
            return false;
        }

        $storage = new SplObjectStorage();
        $storage->attach($document, $this->searchItemLinks($document));

        // Find links on all pages
        if ($totalPages > 1) {
            $nextPage = $document;
            for ($i = ($this->firstPage + 1); $i <= $totalPages; $i++) {
                try {
                    $nextPage = $this->getNextPage($client, $this->buildSearchUrl($query->value, $i), $nextPage);
                    if (empty($nextPage)) {
                        break;
                    }
                    $storage->attach($nextPage, $this->searchItemLinks($nextPage));
                } catch (Exception $e) {
                    JournalStorage::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
                }
            }
        }

        // Loop through all found links
        foreach ($storage as $pageDocument) {
            $urls = $storage[$pageDocument];
            foreach ($urls as $url) {
                try {
                    $itemDocument = $this->getItemPage($client, $url, $pageDocument);
                    foreach ($this->createItem($url, $itemDocument, $pageDocument) as $item) {
                        yield $item;
                    }
                } catch (Exception $e) {
                    JournalStorage::staticPush($this->getName() . ':' . __LINE__ . ' -> ' . $e->getMessage());
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $url, Torrent $file): bool
    {
        if (!$this->hasAuth() || ($this->hasCredentials() && $this->isAvailableAccount())) {
            $client = new CurlClient;
            $client->useCookie($this->getId());

            $content = $this->sendGet($client, $url);
            if ($file->is($content)) {
                $torrent = $file->decode($content);
                if (!empty($torrent) && isset($torrent['info']['name'])) {
                    $file->create($torrent['info']['name'], $content);
                }
            }
        }

        return $file->isAvailable();
    }

    /**
     * @param Document $document
     * @return int
     */
    abstract protected function getTotalPagesFound(Document $document): int;

    /**
     * @param Document $document
     * @return array
     */
    abstract protected function searchItemLinks(Document $document): array;

    /**
     * @param string   $url
     * @param Document $ItemDocument
     * @param Document $pageDocument
     * @return Generator
     */
    abstract protected function createItem(string $url, Document $ItemDocument, Document $pageDocument): Generator;
}