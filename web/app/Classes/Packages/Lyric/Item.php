<?php

namespace Classes\Packages\Lyric;

use Classes\Abstracts\Package\Item as _Item;

class Item extends _Item
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $artist;

    /**
     * @var string
     */
    protected $fetch;

    /**
     * @var string
     */
    protected $page;

    /**
     * @return string
     */
    public function getTitle()
    {
        return ($this->title);
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
    }

    /**
     * @return string
     */
    public function getArtist()
    {
        return ($this->artist);
    }

    /**
     * @param string $artist
     * @return void
     */
    public function setArtist($artist)
    {
        $this->artist = (string)$artist;
    }

    /**
     * @return string
     */
    public function getFetch()
    {
        return ($this->fetch);
    }

    /**
     * @param string $url
     * @return void
     */
    public function setFetch($url)
    {
        $this->fetch = $url;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return ($this->page);
    }

    /**
     * @param string $url
     * @return void
     */
    public function setPage($url)
    {
        $this->page = $url;
    }
}