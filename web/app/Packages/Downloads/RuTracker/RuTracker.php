<?php

namespace Packages\Downloads\RuTracker;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Classes\Packages\Download\Item;

class RuTracker extends Package implements Download
{
    private $name = 'Rutracker';
    private $shortDescription = 'Torrent http://rutracker.org';

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
        return (true);
    }

    public function searchByName($name, Stack $stack)
    {

    }

    public function fetch($url, Torrent $file)
    {
        // TODO: Implement fetch() method.
    }
}
