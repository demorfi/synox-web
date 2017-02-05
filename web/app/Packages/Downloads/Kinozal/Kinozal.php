<?php

namespace Packages\Downloads\Kinozal;

use Classes\Interfaces\Download;
use Classes\Abstracts\Package;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;

class Kinozal extends Package implements Download
{
    private $name = 'Kinozal';
    private $shortDescription = 'Torrent http://kinozal.tv';

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