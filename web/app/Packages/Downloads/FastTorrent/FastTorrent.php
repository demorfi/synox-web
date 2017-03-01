<?php

namespace Packages\Downloads\FastTorrent;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class FastTorrent extends Package implements Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'http://fast-torrent.ru';

    /**
     * @var string
     */
    private $name = 'Fast-Torrent';

    /**
     * @var string
     */
    private $shortDescription = 'Torrent tracker ' . self::SITE_PREFIX;

    /**
     * @var string
     */
    protected $urlQuery = self::SITE_PREFIX . '/search/%s/50/%d.html';

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
        $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), 1));
        $html     = pqInstance($response);

        $total       = (int)$html->find('#search_result i b:first')->text();
        $inPage      = $this->foundElements($html);
        $totalInPage = sizeof($inPage);
        $countPages  = (int)ceil($total / ($totalInPage > 1 ? $totalInPage : 1));

        if ($countPages > 1) {
            for ($i = 2; $i <= $countPages; $i++) {
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
        foreach ($dom->find('.film-list .film-item') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . $item->find('a.film-download')->attr('href');

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
        // Category torrent
        $category = trim($page->find('.info div p a[href]:first')->text());
        if (empty($category)) {
            $category = 'unknown';
        }

        // Torrents rows
        foreach ($page->find('.torrent-row') as $row) {
            $row  = pqElement($row);
            $item = new Item($this);

            // Page torrent
            $item->setPage($url);
            $item->setCategory($category);

            // Title torrent
            $title = trim(str_replace('.torrent', '', $row->find('.torrent-info a[href^=/download/torrent/]')->text()));
            if (!empty($title)) {
                $translation = trim($row->find('.upload1 .c2:first')->text());
                $title .= (!empty($translation) ? ' [' . $translation . ']' : '');
            } else {
                $title = 'unknown';
            }

            $item->setTitle($title);

            // Url download torrent
            $download = $row->find('.torrent-info a[href*="/download/torrent/"]')->attr('href');
            $item->setFetch((!empty($download) ? (self::SITE_PREFIX . $download) : 'unknown'));

            // Torrent size
            $item->setSize((float)$row->attr('size'));

            // Torrent count seeds
            $item->setSeeds((int)trim($row->attr('seeders')));

            // Torrent count peers
            $matches = [];
            preg_match('/(?P<peers>\d+)/', $row->find('.upload1 .c6 font:last')->text(), $matches);
            $item->setPeers((isset($matches['peers']) ? (int)trim($matches['peers']) : 0));

            // Date created torrent
            $item->setDate((new \DateTime())->setTimestamp((int)$row->attr('date')));

            yield $item;
        }
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
