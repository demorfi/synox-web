<?php

namespace Classes\Packages\Download;

use Classes\Abstracts\Package\Item as _Item;

class Item extends _Item
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $seeds;

    /**
     * @var int
     */
    protected $peers;

    /**
     * @var string
     */
    protected $category;

    /**
     * @var string
     */
    protected $fetch;

    /**
     * @var string
     */
    protected $page;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var string
     */
    protected $size;

    /**
     * @var float
     */
    protected $_size;

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
     * @return int
     */
    public function getSeeds()
    {
        return ($this->seeds);
    }

    /**
     * @param int $seeds
     * @return void
     */
    public function setSeeds($seeds)
    {
        $this->seeds = (int)$seeds;
    }

    /**
     * @return int
     */
    public function getPeers()
    {
        return ($this->peers);
    }

    /**
     * @param int $peers
     * @return void
     */
    public function setPeers($peers)
    {
        $this->peers = (int)$peers;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return ($this->category);
    }

    /**
     * @param string $category
     * @return void
     */
    public function setCategory($category)
    {
        $this->category = (string)$category;
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

    /**
     * @return string
     */
    public function getDate()
    {
        return ($this->date);
    }

    /**
     * @param \DateTime $date
     * @return void
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date->format('Y-m-d H:m:s');
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return ($this->size);
    }

    /**
     * @param float $size
     * @return void
     */
    public function setSize($size)
    {
        $this->_size = (float)$size;
        $this->size  = $this->getSizeFormat($this->_size);
    }
}
