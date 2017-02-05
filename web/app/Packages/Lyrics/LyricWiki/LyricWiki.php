<?php

namespace Packages\Lyrics\LyricWiki;

use Classes\Abstracts\Package;
use Classes\Interfaces\Lyric;
use Classes\Packages\Lyric\Content;
use Classes\Packages\Lyric\Stack;

class LyricWiki extends Package implements Lyric
{
    private $name = 'LyricWiki';
    private $shortDescription = 'Song text search http://nnm-club.net';

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

    /**
     * Fetch lyric.
     *
     * @param string $url
     * @param Content $content
     * @return bool
     */
    public function fetch($url, Content $content)
    {
        // TODO: Implement fetch() method.
    }
}