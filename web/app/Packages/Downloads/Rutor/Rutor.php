<?php

namespace Packages\Downloads\Rutor;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class Rutor extends Package implements Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'http://new-ru.org';

    /**
     * @var string
     */
    private $name = 'Rutor';

    /**
     * @var string
     */
    private $shortDescription = 'Torrent tracker ' . self::SITE_PREFIX;

    /**
     * @var string
     */
    private $version = '1.0';

    /**
     * @var string
     */
    protected $urlQuery = self::SITE_PREFIX . '/search/%d/0/100/0/%s';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return ($this->name);
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription()
    {
        return ($this->shortDescription);
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return ($this->version);
    }

    /**
     * @inheritdoc
     */
    public function hasAuth()
    {
        return (false);
    }

    /**
     * @inheritdoc
     */
    public function searchByName($name, Stack $stack)
    {
        $client   = new CurlClient;
        $response = $this->sendGet($client, sprintf($this->urlQuery, 0, urlencode($name)));
        $html     = pqInstance($response);

        $matches = [];
        preg_match('/(?P<result>\d+)\s+\(max\./is', $html->find('fieldset ~ #index')->text(), $matches);

        $total       = (isset($matches['result']) ? (int)trim($matches['result']) : 1);
        $inPage      = $this->foundElements($html);
        $totalInPage = sizeof($inPage);
        $countPages  = (int)ceil($total / ($totalInPage > 1 ? $totalInPage : 1));

        if ($countPages > 1) {
            for ($i = 1; $i <= $countPages; $i++) {
                $response = $this->sendGet($client, sprintf($this->urlQuery, $i, urlencode($name)));
                $inPage   = array_merge($inPage, $this->foundElements(pqInstance($response)));
            }
        }

        foreach ($inPage as $url => $element) {
            $page = pqInstance($this->sendGet($client, $url));
            foreach ($this->createItem($url, $element, $page) as $item) {
                if ($item instanceof Item) {
                    $stack->push($item);
                }
            }
        }

        return (true);
    }

    /**
     * Founds elements in page.
     *
     * @param \phpQueryObject $dom
     * @return array
     */
    protected function foundElements(\phpQueryObject $dom)
    {
        $items = [];
        foreach ($dom->find('#index table .backgr ~ tr') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . $item->find('a[href^=/torrent/]')->attr('href');

            $items[$url] = $item;
        }

        return ($items);
    }

    /**
     * Create item.
     *
     * @param string          $url
     * @param \phpQueryObject $element
     * @param \phpQueryObject $page
     * @return \Generator
     */
    protected function createItem($url, \phpQueryObject $element, \phpQueryObject $page)
    {
        $item  = new Item($this);
        $topic = pqElement($page->find('#details'));

        // Page torrent
        $item->setPage($url);

        // Category torrent
        $category = trim($topic->find('td.header + td > a[href]')->text());
        $item->setCategory((!empty($category) ? $category : 'unknown'));

        // Title torrent
        $title = trim($element->find('.downgif ~ a[href^=/torrent/]')->text());
        $item->setTitle((!empty($title) ? $title : 'unknown'));

        // Url download torrent
        $download = $element->find('a.downgif')->attr('href');
        $item->setFetch((!empty($download) ? (self::SITE_PREFIX . $download) : 'unknown'));

        // Torrent size
        $matches = [];
        preg_match('/\((?P<size>(\d+))\s+Bytes\)/is', $topic->find('td.header + td')->text(), $matches);
        $item->setSize((isset($matches['size']) ? (float)$matches['size'] : 0));

        // Torrent count seeds
        $item->setSeeds((int)filter_var($element->find('td:last span.green')->text(), FILTER_SANITIZE_NUMBER_INT));

        // Torrent count peers
        $item->setPeers((int)filter_var($element->find('td:last span.red')->text(), FILTER_SANITIZE_NUMBER_INT));

        // Date created torrent
        $matches = [];
        preg_match(
            '/(?P<time>\d{2}\-\d{2}\-\d{4}\s+\d{2}:\d{2}:\d{2})/is',
            $topic->find('td.header + td')->text(),
            $matches
        );

        $item->setDate((new \DateTime())->setTimestamp(strtotime(isset($matches['time']) ? $matches['time'] : 'now')));

        yield $item;
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {
        $client  = new CurlClient;
        $content = $this->sendGet($client, $url);

        if ($file->is($content)) {
            $torrent = $file->decode($content);
            if (!empty($torrent) && isset($torrent['info']['name'])) {
                $file->create($torrent['info']['name'], $content);
            }
        }

        return ($file->isAvailable());
    }
}
