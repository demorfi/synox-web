<?php

namespace Classes\Packages\Lyric;

use Classes\Abstracts\Package\Item as _Item;

class Item extends _Item
{
    /**
     * Title.
     *
     * @var string
     */
    protected $title;

    /**
     * Artist.
     *
     * @var string
     */
    protected $artist;

    /**
     * Url fetch.
     *
     * @var string
     */
    protected $fetch;

    /**
     * Url page.
     *
     * @var string
     */
    protected $page;

    /**
     * Lyrics content.
     *
     * @var string
     */
    protected $lyrics;

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return ($this->title);
    }

    /**
     * Set title.
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
    }

    /**
     * Get artist.
     *
     * @return string
     */
    public function getArtist()
    {
        return ($this->artist);
    }

    /**
     * Set artist.
     *
     * @param string $artist
     * @return void
     */
    public function setArtist($artist)
    {
        $this->artist = (string)$artist;
    }

    /**
     * Get lyric content.
     *
     * @return string
     */
    public function getLyrics()
    {
        return ($this->lyrics);
    }

    /**
     * Set lyric content.
     *
     * @param string $lyrics
     * @return void
     */
    public function setLyrics($lyrics)
    {
        $this->lyrics = (string)$lyrics;
    }

    /**
     * Get url fetch.
     *
     * @return string
     */
    public function getFetch()
    {
        return ($this->fetch);
    }

    /**
     * Set url fetch.
     *
     * @param string $url
     * @return void
     */
    public function setFetch($url)
    {
        $this->fetch = $url;
    }

    /**
     * Get url page.
     *
     * @return string
     */
    public function getPage()
    {
        return ($this->page);
    }

    /**
     * Set url page.
     *
     * @param string $url
     * @return void
     */
    public function setPage($url)
    {
        $this->page = $url;
    }
}
