<?php

namespace Classes\Packages\Download;

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
     * Count seeds.
     *
     * @var int
     */
    protected $seeds;

    /**
     * Count peers.
     *
     * @var int
     */
    protected $peers;

    /**
     * Category.
     *
     * @var string
     */
    protected $category;

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
     * Date.
     *
     * @var string
     */
    protected $date;

    /**
     * Formatted size.
     *
     * @var string
     */
    protected $size;

    /**
     * Size.
     *
     * @var float
     */
    protected $_size;

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
     * Get count seeds.
     *
     * @return int
     */
    public function getSeeds()
    {
        return ($this->seeds);
    }

    /**
     * Set count seeds.
     *
     * @param int $seeds
     * @return void
     */
    public function setSeeds($seeds)
    {
        $this->seeds = (int)$seeds;
    }

    /**
     * Get count peers.
     *
     * @return int
     */
    public function getPeers()
    {
        return ($this->peers);
    }

    /**
     * Set count peers.
     *
     * @param int $peers
     * @return void
     */
    public function setPeers($peers)
    {
        $this->peers = (int)$peers;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return ($this->category);
    }

    /**
     * Set category.
     *
     * @param string $category
     * @return void
     */
    public function setCategory($category)
    {
        $this->category = (string)$category;
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

    /**
     * Get date.
     *
     * @return string
     */
    public function getDate()
    {
        return ($this->date);
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     * @return void
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date->format('Y-m-d H:m:s');
    }

    /**
     * Get formatted size.
     *
     * @return string
     */
    public function getSize()
    {
        return ($this->size);
    }

    /**
     * Set size.
     *
     * @param float $size
     * @return void
     */
    public function setSize($size)
    {
        $this->_size = (float)$size;
        $this->size  = $this->getSizeFormat($this->_size);
    }
}
