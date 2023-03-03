<?php declare(strict_types=1);

namespace App\Package\Lyrics;

use App\Abstracts\PackageItem as PackageItemAbstract;

class Item extends PackageItemAbstract
{
    /**
     * @var string
     */
    protected string $artist = 'Unknown artist';

    /**
     * @var string
     */
    protected string $content;

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     * @return void
     */
    public function setArtist(string $artist): void
    {
        $this->artist = $artist ?: $this->artist;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
