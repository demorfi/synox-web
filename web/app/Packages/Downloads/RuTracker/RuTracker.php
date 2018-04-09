<?php

namespace Packages\Downloads\RuTracker;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class RuTracker extends Package implements Download
{
    use Client;

    /**
     * @var string
     */
    const SITE_PREFIX = 'http://rutracker.org/forum';

    /**
     * @var string
     */
    private $name = 'Rutracker';

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
    protected $urlQuery = self::SITE_PREFIX . '/tracker.php?nm=%s';

    /**
     * @var string
     */
    protected $urlLogin = self::SITE_PREFIX . '/login.php';

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
        if (!$html->find('.logged-in-as-uname')->length) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'login_username' => $this->getSetting('username'),
                    'login_password' => $this->getSetting('password'),
                    'ses_short'      => '0',
                    'login'          => ''
                ]
            );

            $html = pqInstance($response);
            return ($html->find('.logged-in-as-uname')->length ? true : false);
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
            $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name)));
            $html     = pqInstance($response);

            $matches = [];
            preg_match(
                '/(?P<result>\d+)\s+\(/',
                $html->find('#main_content table .vBottom .med:first')->text(),
                $matches
            );

            $total       = (isset($matches['result']) ? (int)trim($matches['result']) : 1);
            $inPage      = $this->foundElements($html);
            $totalInPage = sizeof($inPage);
            $countPages  = (int)ceil($total / ($totalInPage > 1 ? $totalInPage : 1));

            if ($countPages > 1) {
                for ($i = 2; $i <= $countPages; $i++) {
                    $link = $html->find('#main_content .vBottom .pg-jump-menu ~ a:not(:last):eq(' . ($i - 2) . ')')
                        ->attr('href');

                    $safeLink = html_entity_decode($link) . '&nm=' . urlencode($name);

                    $nextPage = self::SITE_PREFIX . '/' . $safeLink;
                    $response = $this->sendGet($client, $nextPage);
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
        $items = [];
        foreach ($dom->find('.forumline tr.hl-tr') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . '/' . $item->find('a.tLink')->attr('href');

            $items[$url] = $item;
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
        $topic = pqElement($page->find('#topic_main .row1:eq(0) .post_wrap .post_body'));

        // Page torrent
        $item->setPage($url);

        // Category torrent
        $category = trim($page->find('#main_content table .vBottom table .nav a:last')->text());
        $item->setCategory((!empty($category) ? $category : 'unknown'));

        // Title torrent
        $title = trim($page->find('#main_content table .vBottom .maintitle a:first')->text());
        $item->setTitle((!empty($title) ? $title : 'unknown'));

        // Url download torrent
        $download = $topic->find('#tor-reged .attach a.dl-link')->attr('href');
        $item->setFetch((!empty($download) ? (self::SITE_PREFIX . '/' . $download) : 'unknown'));

        // Torrent size
        $item->setSize((float)$element->find('td.tor-size u')->text());

        // Torrent count seeds
        $item->setSeeds((int)trim($element->find('td b.seedmed')->text()));

        // Torrent count peers
        $item->setPeers((int)trim($element->find('td.leechmed b')->text()));

        // Date created torrent
        $item->setDate((new \DateTime())->setTimestamp((int)$element->find('.row4:last u')->text()));

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
