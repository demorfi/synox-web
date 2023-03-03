<?php declare(strict_types=1);

namespace App\Interfaces;

use App\Package\Lyrics\Content;

interface Lyrics extends Package
{
    /**
     * Fetch lyric.
     *
     * @param string  $url
     * @param Content $content
     * @return bool
     */
    public function fetch(string $url, Content $content): bool;
}
