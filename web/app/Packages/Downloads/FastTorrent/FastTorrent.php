<?php

namespace Packages\Downloads\FastTorrent;

use Classes\Interfaces\Download;
use Classes\Abstracts\Package;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Torrent;
use Framework\Traits\Client;
use Framework\Components\Client\Curl as CurlClient;

class FastTorrent extends Package implements Download
{
    use Client;

    private $name = 'Fast-Torrent';
    private $shortDescription = 'Torrent tracker http://fast-torrent.ru';
    protected $urlQuery = 'http://www.fast-torrent.ru/search/%s/50/%d.html';

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
        $client = new CurlClient;
        $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), 1));

        // phpQuery($response)
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {

    }
}
