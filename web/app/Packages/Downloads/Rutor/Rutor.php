<?php

namespace Packages\Downloads\Rutor;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;

class Rutor extends Package implements Download
{
    private $name = 'Rutor';
    private $shortDescription = 'Torrent http://new-rutor.org';

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
        // TODO: Implement searchByName() method.
    }

    public function fetch($url, Torrent $file)
    {
        // TODO: Implement fetch() method.
    }
}