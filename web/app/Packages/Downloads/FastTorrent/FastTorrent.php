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

    private $name = 'Fast-Torrent';

    private $shortDescription = 'Torrent tracker http://fast-torrent.ru';

    protected $urlQuery = 'http://www.fast-torrent.ru/search/%s/50/%d.html';

    /**
     * Prefix url.
     *
     * @var string
     * @access protected
     */
    protected $pagePrefix = 'http://www.fast-torrent.ru';

    public function getName()
    {
        return ($this->name);
    }

    public function getShortDescription()
    {
        return ($this->shortDescription);
    }

    public function hasAuth()
    {
        return (false);
    }

    public function searchByName($name, Stack $stack)
    {
        $client   = new CurlClient;
        $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), 1));

        $html = pqInstance($response);

        $founds      = (int)$html->find('#search_result i b:first')->text();
        $itemsInPage = $this->foundItems($html);
        $countItems  = sizeof($itemsInPage);

        $countPages = (int)ceil($founds / ($countItems > 1 ? $countItems : 1));
        if ($countPages > 1) {
            for ($i = 2; $i <= $countPages; $i++) {
                $response    = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), $i));
                $itemsInPage = array_merge($itemsInPage, $this->foundItems(pqInstance($response)));
            }
        }

        foreach ($itemsInPage as $urlItem) {
            $page = pqInstance($this->sendGet($client, $urlItem));
            foreach ($page->find('.torrent-row') as $row) {
                $item = $this->createItem(pqElement($row));

                // Page torrent
                $item->setPage($urlItem);

                // Category torrent
                $category = trim($page->find('.info div p a[href]:first')->text());
                $item->setCategory((!empty($category) ? $category : 'unknown'));

                $stack->push($item);
            }
        }
    }

    protected function foundItems(\phpQueryObject $dom)
    {
        $items = [];
        foreach ($dom->find('.film-list .film-item') as $item) {
            $items[] = ($this->pagePrefix . pqElement($item)->find('a.film-download')->attr('href'));
        }

        return ($items);
    }

    protected function createItem(\phpQueryObject $dom)
    {
        $item = new Item($this);

        // Title torrent
        $title = trim(str_replace('.torrent', '', $dom->find('.torrent-info a[href^=/download/torrent/]')->text()));
        if (!empty($title)) {
            $translation = trim($dom->find('.upload1 .c2:first')->text());
            $title .= (!empty($translation) ? ' [' . $translation . ']' : '');
        } else {
            $title = 'unknown';
        }

        $item->setTitle($title);

        // Url download torrent
        $download = $dom->find('.torrent-info a[href^=/download/torrent/]')->attr('href');
        $item->setFetch((!empty($download) ? ($this->pagePrefix . $download) : 'unknown'));

        // Torrent size
        $item->setSize((float)$dom->attr('size'));

        // Torrent count seeds
        $item->setSeeds((int)trim($dom->attr('seeders')));

        // Torrent count peers
        $matches = [];
        preg_match('/(?P<peers>\d+)/', $dom->find('.upload1 .c6 font:last')->text(), $matches);
        $item->setPeers((isset($matches['peers']) ? (int)trim($matches['peers']) : 0));

        // Date created torrent
        $item->setDate((new \DateTime())->setTimestamp((int)$dom->attr('date')));

        return ($item);
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {

    }


}
