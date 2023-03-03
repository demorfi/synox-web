<?php declare(strict_types=1);

namespace App\Interfaces;

use App\Package\Download\Torrent;

interface Download extends Package
{
    /**
     * Fetch torrent.
     *
     * @param string  $url
     * @param Torrent $file
     * @return bool
     */
    public function fetch(string $url, Torrent $file): bool;
}
