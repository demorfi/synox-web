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
    private $name = 'TestDownload';

    /**
     * @var string
     */
    private $shortDescription = 'Test download';

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
    public function hasAuth()
    {
        return (false);
    }

    /**
     * @inheritdoc
     */
    public function searchByName($name, Stack $stack)
    {
        $index = 5;
        while ($index--) {
            $item = new Item($this);
            $item->setTitle('Test ' . $name . ' ' . $index);
            $item->setCategory('Test category ' . $index);
            $item->setPeers(rand(1, 100));
            $item->setSeeds(rand(1, 100));
            $item->setSize(rand(1000000, 9999999));
            $item->setDate(new \DateTime());
            $item->setFetch('http://test/download?id=' . $index);
            $item->setPage('http://test/page?id=' . $index);
            $stack->push($item);
        }
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {
        $file->create('test.torrent', 'announce');
    }
}
