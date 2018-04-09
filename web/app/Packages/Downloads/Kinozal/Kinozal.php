<?php

namespace Packages\Downloads\Kinozal;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class Kinozal extends Package implements Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'http://kinozal.tv';

    /**
     * @var string
     */
    private $name = 'Kinozal';

    /**
     * @var string
     */
    private $shortDescription = 'Torrent tracker ' . self::SITE_PREFIX;

    /**
     * @var string
     */
    private $version = '1.0';

    /**
     * @var string
     */
    protected $urlQuery = self::SITE_PREFIX . '/browse.php?s=%s&g=0&page=%d';

    /**
     * @var string
     */
    protected $urlLogin = self::SITE_PREFIX . '/takelogin.php';

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
    public function getVersion()
    {
        return ($this->version);
    }

    /**
     * @inheritdoc
     */
    public function hasAuth()
    {
        return (true);
    }

    /**
     * Check available account.
     *
     * @return bool
     */
    protected function isAvailableAccount()
    {
        $client = new CurlClient;
        $client->useCookie(__CLASS__);
        $response = $this->sendGet($client, self::SITE_PREFIX);

        $html = pqInstance($response);
        if (!$html->find('a[href^="/logout"]')->length) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username' => $this->getSetting('username'),
                    'password' => $this->getSetting('password')
                ]
            );

            $html = pqInstance($response);
            return ($html->find('a[href^="/logout"]')->length ? true : false);
        }

        return (true);
    }

    /**
     * @inheritdoc
     */
    public function searchByName($name, Stack $stack)
    {
        if ($this->isAvailableAccount()) {
            $client = new CurlClient;
            $client->useCookie(__CLASS__);
            $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), 0));
            $html     = pqInstance($response);

            $matches = [];
            preg_match('/\s+(?P<result>\d+)\s+/', $html->find('.content .tables1 tr:last td')->text(), $matches);

            $total       = (isset($matches['result']) ? (int)trim($matches['result']) : 1);
            $inPage      = $this->foundElements($html);
            $totalInPage = sizeof($inPage);
            $countPages  = (int)ceil($total / ($totalInPage > 1 ? $totalInPage : 1));

            if ($countPages > 1) {
                for ($i = 1; $i <= $countPages; $i++) {
                    $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name), $i));
                    $inPage   = array_merge($inPage, $this->foundElements(pqInstance($response)));
                }
            }

            foreach ($inPage as $url => $element) {
                $page = pqInstance(iconv('cp1251', 'UTF-8', $this->sendGet($client, $url)));
                foreach ($this->createItem($url, $element, $page) as $item) {
                    if ($item instanceof Item) {
                        $stack->push($item);
                    }
                }
            }

            return (true);
        }

        return (false);
    }

    /**
     * Founds elements in page.
     *
     * @param \phpQueryObject $dom
     * @return array
     */
    protected function foundElements(\phpQueryObject $dom)
    {
        $items     = [];
        $searchBar = $dom->find('.content .bx1_0 table');

        foreach ($dom->find('.content .t_peer tr:not(:first)') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . $item->find('.nam a')->attr('href');

            $items[$url] = pqElement($item)->append($searchBar->html());
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
        $item  = new Item($this);
        $topic = pqElement($page->find('.content .mn_wrap'));

        // Page torrent
        $item->setPage($url);

        // Category torrent
        $matches = [];
        preg_match('/(?P<id>\d+)\./is', $element->find('.bt img')->attr('src'), $matches);
        $categories = $element->find('select[name="c"] option');

        $category = (isset($matches['id']) && $categories->length)
            ? trim(pqElement($categories)->filter('[value=' . $matches['id'] . ']')->text())
            : 'unknown';
        $item->setCategory($category);

        // Title torrent
        $title = trim($element->find('.nam a')->text());
        $item->setTitle((!empty($title) ? $title : 'unknown'));

        // Url download torrent
        $download = $topic->find('.mn1_content table a[href*="/download"]')->attr('href');
        $item->setFetch((!empty($download) ? $download : 'unknown'));

        // Torrent size
        $matches = [];
        preg_match('/\((?P<size>(\d|,)+)\)/is', $topic->find('.mn1_menu li .floatright')->text(), $matches);
        $item->setSize((isset($matches['size']) ? (float)str_replace(',', '', $matches['size']) : 0));

        // Torrent count seeds
        $item->setSeeds((int)trim($element->find('.sl_s')->text()));

        // Torrent count peers
        $item->setPeers((int)trim($element->find('.sl_p')->text()));

        // Date created torrent
        $matches = [];
        preg_match(
            '/(?P<date>\d{2}\.\d{2}\.\d{4}).*(?P<time>\d{2}:\d{2})/is',
            $element->find('.sl_p ~ .s')->text(),
            $matches
        );

        $timestamp = strtotime(
            isset($matches['date'], $matches['time'])
                ? ($matches['date'] . ' ' . $matches['time'])
                : 'now'
        );

        $item->setDate((new \DateTime())->setTimestamp($timestamp));

        yield $item;
    }

    /**
     * @inheritdoc
     */
    public function fetch($url, Torrent $file)
    {
        if ($this->isAvailableAccount()) {
            $client = new CurlClient;
            $client->useCookie(__CLASS__);

            $content = $this->sendGet($client, $url);
            if ($file->is($content)) {
                $torrent = $file->decode($content);
                if (!empty($torrent) && isset($torrent['info']['name'])) {
                    $file->create($torrent['info']['name'], $content);
                }
            }
        }

        return ($file->isAvailable());
    }
}
