<?php

namespace Classes\Interfaces;

use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;

interface Download extends Package
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
     * Fetch torrent.
     *
     * @param string  $url
     * @param Torrent $file
     * @return bool
     */
    public function fetch($url, Torrent $file);
}
