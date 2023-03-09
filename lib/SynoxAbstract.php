<?php declare(strict_types=1);

class SynoxAbstract
{
    /**
     * @var string
     */
    private $lyricsId;

    /**
     * @return string
     */
    public function getLyricsId(): string
    {
        return $this->lyricsId;
    }

    /**
     * @param string $title    Title torrent
     * @param string $download Url to download torrent
     * @param float  $size     Size files in torrent
     * @param string $datetime Date create torrent
     * @param string $page     Url torrent page
     * @param string $hash     Hash item
     * @param int    $seeds    Count torrent seeds
     * @param int    $leeches  Count torrent leeches
     * @param string $category Torrent category
     * @return void
     */
    public function addResult(
        string $title,
        string $download,
        float $size,
        string $datetime,
        string $page,
        string $hash,
        int $seeds,
        int $leeches,
        string $category
    ): void {
        var_dump(
            [
                'result' => compact(
                    'title',
                    'download',
                    'size',
                    'datetime',
                    'page',
                    'hash',
                    'seeds',
                    'leeches',
                    'category'
                )
            ]
        );
    }

    /**
     * @param string  $artist        Artist song
     * @param string  $title         Title song
     * @param string  $id            Id song
     * @param ?string $partialLyrics Partial lyric song
     * @return void
     */
    public function addTrackInfoToList(string $artist, string $title, string $id, string $partialLyrics = null): void
    {
        var_dump(['track-info' => compact('artist', 'title', 'id', 'partialLyrics')]);
        $this->lyricsId = $id;
    }

    /**
     * @param string $lyric Lyric content
     * @param string $id    Lyric id
     * @return void
     */
    public function addLyrics(string $lyric, string $id): void
    {
        var_dump(['lyrics' => compact('lyric', 'id')]);
    }
}
