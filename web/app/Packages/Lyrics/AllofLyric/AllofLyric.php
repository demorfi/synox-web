<?php

namespace Packages\Lyrics\AllofLyric;

use Classes\Abstracts\Package;
use Classes\Interfaces\Lyric;
use Classes\Packages\Lyric\Content;
use Classes\Packages\Lyric\Item;
use Classes\Packages\Lyric\Stack;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class AllofLyric extends Package implements Lyric
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'https://alloflyric.com';

    /**
     * @var string
     */
    private $name = 'AllofLyric';

    /**
     * @var string
     */
    private $shortDescription = 'Song text search ' . self::SITE_PREFIX;

    /**
     * @var string
     */
    protected $urlQuery = self::SITE_PREFIX . '/search/?s=%s';

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
            return ([$artist, $song]);
        }

        return ([]);
    }

    /**
     * @inheritdoc
     */
    public function searchByName($name, Stack $stack)
    {
        $client   = new CurlClient;
        $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name)));
        $html     = pqInstance($response);

        $total  = 10;
        $inPage = $this->foundElements($html);

        foreach ($inPage as $url => $element) {
            if (!$total) {
                break;
            }

            $page = pqInstance($this->sendGet($client, $url));
            foreach ($this->createItem($url, $element, $page) as $item) {
                if ($item instanceof Item) {
                    $stack->push($item);
                    $total--;
                }
            }
        }

        return (true);
    }

    /**
     * Founds elements in page.
     *
     * @param \phpQueryObject $dom
     * @return array
     */
    protected function foundElements(\phpQueryObject $dom)
    {
        $items = [];
        foreach ($dom->find('.container table tr') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . $item->find('td:last a')->attr('href');
            if (strpos($url, '/song/') !== false) {
                $items[$url] = $item;
            }
        }

        return ($items);
    }

    /**
     * Create item.
     *
     * @param string          $url
     * @param \phpQueryObject $element
     * @param \phpQueryObject $page
     * @return \Generator
     */
    protected function createItem($url, \phpQueryObject $element, \phpQueryObject $page)
    {
        $item = new Item($this);

        // Artist and Title lyric
        list ($artist, $song) = $this->getArtistAndSong($element->find('td:last a')->text());
        $item->setArtist($artist);
        $item->setTitle($song);

        // Page lyric
        $item->setPage($url);

        // Page text lyric
        $item->setFetch($url);

        // Lyric short
        $lyric = Content::filter($page->find('.jumbotron .container > p')->html());
        $item->setLyrics(substr(strip_tags($lyric), 0, 100) . '...');

        yield $item;
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Content $content)
    {
        $client   = new CurlClient;
        $response = $this->sendGet($client, $url);
        $page     = pqInstance($response);

        $content->add($page->find('.jumbotron .container > p')->html());
        return ($content->isAvailable());
    }
}
