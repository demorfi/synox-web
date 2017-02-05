<?php

namespace Packages\Lyrics\Bananan;

use Classes\Abstracts\Package;
use Classes\Interfaces\Lyric;
use Classes\Packages\Lyric\Stack;
use Classes\Packages\Lyric\Item;
use Classes\Packages\Lyric\Content;

class Bananan extends Package implements Lyric
{
    private $name = 'Bananan';
    private $shortDescription = 'Song text search http://bananan.org';

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
    public function fetch($url, Content $content)
    {

    }
}
