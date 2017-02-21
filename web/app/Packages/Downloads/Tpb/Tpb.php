<?php

namespace Packages\Downloads\Tpb;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class Tpb extends Package implements Download
{
    use Client;

    const SITE_PREFIX = 'https://thepiratebayz.org';

    private $name = 'The Pirate Bay';

    private $shortDescription = 'Torrent tracker ' . self::SITE_PREFIX;

    protected $urlQuery = self::SITE_PREFIX . '/search/%s/%d/7/';

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
        $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), 0));
        $html = pqInstance($response);
        // TODO This
    }

    public function fetch($url, Torrent $file)
    {
        // TODO: Implement fetch() method.
    }
}
