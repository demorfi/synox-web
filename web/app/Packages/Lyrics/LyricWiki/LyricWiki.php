<?php

namespace Packages\Lyrics\LyricWiki;

use Classes\Abstracts\Package;
use Classes\Interfaces\Lyric;
use Classes\Packages\Lyric\Content;
use Classes\Packages\Lyric\Item;
use Classes\Packages\Lyric\Stack;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class LyricWiki extends Package implements Lyric
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'http://lyrics.wikia.com';

    /**
     * @var string
     */
    private $name = 'LyricWiki';

    /**
     * @var string
     */
    private $shortDescription = 'Song text search ' . self::SITE_PREFIX;

    /**
     * @var string
     */
    protected $urlQuery = self::SITE_PREFIX . '/api.php?action=lyrics&artist=%s&song=%s&fmt=realjson';

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
     * Get artist and song.
     *
     * @param string $name
     * @return array
     */
    protected function getArtistAndSong($name)
    {
        $separator = (strpos($name, ' - ') !== false ? ' - ' : (strpos($name, ': ') !== false ? ': ' : ''));
        if (!empty($separator)) {
            list ($artist, $song) = explode($separator, $name);

            $client   = new CurlClient;
            $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($artist), ''));
            $content  = json_decode(deUnicode($response), true);
            return (isset($content['artist']) ? [$content['artist'], $song] : []);
        }

        return ([]);
    }

    /**
     * @inheritdoc
     */
    public function searchByName($name, Stack $stack)
    {
        list ($artist, $song) = $this->getArtistAndSong($name);
        if (!empty($artist) && !empty($song)) {
            $client   = new CurlClient;
            $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($artist), urlencode($song)));
            $content  = json_decode(iconv('UTF-8', 'ISO-8859-1', deUnicode($response)), true);

            if (isset($content['lyrics']) && $content['lyrics'] !== 'Not found') {
                $item = new Item($this);
                $item->setTitle($content['song']);
                $item->setArtist($content['artist']);
                $item->setLyrics($content['lyrics']);
                $item->setPage($content['url']);
                $item->setFetch($content['url']);
                $stack->push($item);
            }

            return (true);
        }

        return (false);
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Content $content)
    {
        $client   = new CurlClient;
        $response = html_entity_decode($this->sendGet($client, $url . '?action=edit'), ENT_QUOTES, 'UTF-8');

        $matches = [];
        preg_match('/<lyrics>(?P<lyrics>.*)<\/lyrics>/is', $response, $matches);

        if (isset($matches['lyrics']) && !empty($matches['lyrics'])) {
            $content->add($matches['lyrics']);
        }

        return ($content->isAvailable());
    }
}
