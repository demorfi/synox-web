<?php

namespace Packages\Downloads\TestDownload;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;

class TestDownload extends Package implements Download
{
    /**
     * @var string
     */
    private $name = 'Test Download';

    /**
     * @var string
     */
    private $shortDescription = 'Test download';

    /**
     * @var string
     */
    private $version = '1.0';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return ($this->name);
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription()
    {
        return ($this->shortDescription);
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return ($this->version);
    }

    /**
     * @inheritdoc
     */
    public function hasAuth()
    {
        return (false);
    }

    /**
     * @inheritdoc
     */
    public function searchByName($name, Stack $stack)
    {
        $index = 6;
        while (--$index) {
            $item = new Item($this);
            $item->setTitle('Test ' . $name . ' ' . $index);
            $item->setCategory('Test category ' . $index);
            $item->setPeers(rand(1, 100));
            $item->setSeeds(rand(1, 100));
            $item->setSize(rand(1000000, 9999999));
            $item->setDate(new \DateTime());
            $item->setFetch('http://synox.loc/?id=' . $this->name . '&fetch=' . $index);
            $item->setPage('http://synox.loc/' . $index);
            $stack->push($item);
        }

        return (true);
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {
        $file->create('test', 'd8:announce');
        return ($file->isAvailable());
    }
}
