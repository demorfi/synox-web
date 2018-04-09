<?php

namespace Classes\Interfaces;

use Classes\Packages\Lyric\Content;
use Classes\Packages\Lyric\Stack;

interface Lyric extends Package
{
    /**
     * Search.
     *
     * @param string $name
     * @param Stack  $stack
     * @return bool
     */
    public function searchByName($name, Stack $stack);

    /**
     * Fetch lyric.
     *
     * @param string  $url
     * @param Content $content
     * @return bool
     */
    public function fetch($url, Content $content);
}
