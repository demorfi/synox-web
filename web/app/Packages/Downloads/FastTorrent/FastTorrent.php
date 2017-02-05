<?php

namespace Packages\Downloads\FastTorrent;

use Classes\Interfaces\Download;
use Classes\Abstracts\Package;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Torrent;

class FastTorrent extends Package implements Download
{
    private $name = 'Fast-Torrent';
    private $shortDescription = 'Torrent http://fast-torrent.ru';

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

    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {

    }
}
