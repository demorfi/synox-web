<?php

namespace Packages\Downloads\Tpb;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class Tpb extends Package implements Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'https://proxybay.win';

    /**
     * @var string
     */
    private $name = 'The Pirate Bay';

    /**
     * @var string
     */
    private $shortDescription = 'Torrent tracker thepiratebay - ' . self::SITE_PREFIX;

    /**
     * @var string
     */
    protected $urlQuery = self::SITE_PREFIX . '/search/%s/%d/7/';

    /**
     * @var string
     */
    protected $urlFetch = 'http://itorrents.org/torrent/%s.torrent';

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
        $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), 0));
        $html     = pqInstance($response);

        $matches = [];
        preg_match('/(?P<result>\d+)\s+found\)/is', $html->find('#header ~ h2')->text(), $matches);

        $total       = (isset($matches['result']) ? (int)trim($matches['result']) : 1);
        $inPage      = $this->foundElements($html);
        $totalInPage = sizeof($inPage);
        $countPages  = (int)ceil($total / ($totalInPage > 1 ? $totalInPage : 1));

        if ($countPages > 1) {
            for ($i = 1; $i <= $countPages; $i++) {
                $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), $i));
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
        foreach ($dom->find('#searchResult tr:not(.header)') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . $item->find('a.detLink')->attr('href');

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
        $item = new Item($this);

        // Page torrent
        $item->setPage($url);

        // Category torrent
        $category = trim($element->find('td:first a:last')->text());
        $item->setCategory((!empty($category) ? $category : 'unknown'));

        // Title torrent
        $title = trim($element->find('a.detLink')->text());
        $item->setTitle((!empty($title) ? $title : 'unknown'));

        // Url download torrent
        $download = $page->find('.download a[href^="magnet:"]')->attr('href');
        $item->setFetch((!empty($download) ? $download : 'unknown'));

        // Torrent size
        $matches = [];
        preg_match('/\((?P<size>\d+).+Bytes\)/is', $page->find('#main-content #details dl dd')->text(), $matches);
        $item->setSize((isset($matches['size']) ? (float)trim($matches['size']) : 0));

        // Torrent count seeds
        $item->setSeeds((int)trim($element->find('td:eq(2)')->text()));

        // Torrent count peers
        $item->setPeers((int)trim($element->find('td:last')->text()));

        // Date created torrent
        $matches = [];
        preg_match('/(?P<time>\d{4}\-\d{2}\-\d{2}\s+\d{2}:\d{2}:\d{2}\s)/is', $page->find('dl dd')->text(), $matches);

        $timestamp = strtotime(
            isset($matches['time'])
                ? trim($matches['time'])
                : 'now'
        );

        $item->setDate((new \DateTime())->setTimestamp($timestamp));

        yield $item;
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {
        $matches = [];
        preg_match('/(?P<hash>[a-z0-9]{40})/is', $url, $matches);
        if (isset($matches['hash']) && !empty($matches['hash'])) {
            $client  = new CurlClient;
            $content = $this->sendGet($client, sprintf($this->urlFetch, $matches['hash']));

            if ($file->is($content)) {
                $torrent = $file->decode($content);
                if (!empty($torrent) && isset($torrent['info']['name'])) {
                    $file->create($torrent['info']['name'], $content);
                }
            }
        }

        return ($file->isAvailable());
    }
}
