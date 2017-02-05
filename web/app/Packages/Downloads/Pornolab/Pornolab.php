<?php

namespace Packages\Downloads\Pornolab;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;

class Pornolab extends Package implements Download
{
    private $name = 'Pornolab';
    private $shortDescription = 'Torrent http://pornolab.net';

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
        // TODO: Implement searchByName() method.
    }

    public function fetch($url, Torrent $file)
    {
        // TODO: Implement fetch() method.
    }
}