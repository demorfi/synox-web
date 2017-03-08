<?php

namespace Packages\Lyrics\TestLyric;

use Classes\Abstracts\Package;
use Classes\Interfaces\Lyric;
use Classes\Packages\Lyric\Content;
use Classes\Packages\Lyric\Item;
use Classes\Packages\Lyric\Stack;

class TestLyric extends Package implements Lyric
{
    /**
     * @var string
     */
    private $name = 'TestLyric';

    /**
     * @var string
     */
    private $shortDescription = 'Test lyric';

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
            $item->setArtist('Test artist ' . $index);
            $item->setLyrics('Test lyrics short ' . $index);
            $item->setPage('http:/test/page?id=' . $index);
            $item->setFetch('http:/test/lyric?id=' . $index);
            $stack->push($item);
        }

        return (true);
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Content $content)
    {
        $content->add('test <br /> content');
        return ($content->isAvailable());
    }
}
