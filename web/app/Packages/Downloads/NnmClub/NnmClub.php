<?php

namespace Packages\Downloads\NnmClub;

use Classes\Abstracts\Package;
use Classes\Interfaces\Download;
use Classes\Packages\Download\Item;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Components\Client\Curl as CurlClient;
use Framework\Traits\Client;

class NnmClub extends Package implements Download
{
    use Client;

    const SITE_PREFIX = 'http://nnm-club.me/forum';

    private $name = 'NNM Club';

    private $shortDescription = 'Torrent tracker ' . self::SITE_PREFIX;

    protected $urlQuery = self::SITE_PREFIX . '/tracker.php?nm=%s';

    protected $urlLogin = self::SITE_PREFIX . '/login.php';

    public function getName()
    {
        return ($this->name);
    }

    public function getShortDescription()
    {
        return ($this->shortDescription);
    }

    public function hasAuth()
    {
        return (true);
    }

    protected function isAvailableAccount()
    {
        $client = new CurlClient;
        $client->useCookie(__CLASS__);
        $response = $this->sendGet($client, self::SITE_PREFIX);

        $html = pqInstance($response);
        if (!$html->find('a[href^="login.php?logout"]')->length) {
            $response = $this->sendPost(
                $client,
                $this->urlLogin,
                [
                    'username'  => $this->getSetting('username'),
                    'password'  => $this->getSetting('password'),
                    'autologin' => '1',
                    'login'     => ''
                ]
            );

            $html = pqInstance($response);
            return ($html->find('a[href^="login.php?logout"]')->length ? true : false);
        }

        return (true);
    }

    public function searchByName($name, Stack $stack)
    {
        if ($this->isAvailableAccount()) {
            $client = new CurlClient;
            $client->useCookie(__CLASS__);
            $response = $this->sendGet($client, sprintf($this->urlQuery, urlencode($name)));

            $html = pqInstance($response);

            $matches = [];
            preg_match('/(?P<result>\d+)\s+\(max/is', $html->find('#search_form table .nav')->text(), $matches);

            $total       = (!empty($matches['result']) ? (int)trim($matches['result']) : 1);
            $inPage      = $this->foundElements($html);
            $totalInPage = sizeof($inPage);
            $countPages  = (int)ceil($total / ($totalInPage > 1 ? $totalInPage : 1));

            if ($countPages > 1) {
                for ($i = 2; $i <= $countPages; $i++) {
                    $link = $html->find('.forumline ~ table .nav b ~ a:not(:last):eq(' . ($i - 2) . ')')
                        ->attr('href');

                    $safeLink = preg_replace(
                        '/&nm=(.*)&start/',
                        '&nm=' . urlencode($name) . '&start',
                        html_entity_decode($link)
                    );

                    $nextPage = self::SITE_PREFIX . '/' . $safeLink;
                    $response = $this->sendGet($client, $nextPage);
                    $inPage   = array_merge($inPage, $this->foundElements(pqInstance($response)));
                }
            }

            foreach ($inPage as $url => $element) {
                $page = pqInstance($this->sendGet($client, $url));
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

    protected function foundElements(\phpQueryObject $dom)
    {
        $items = [];
        foreach ($dom->find('.forumline.tablesorter tbody tr') as $item) {
            $item = pqElement($item);
            $url  = self::SITE_PREFIX . '/' . $item->find('.genmed a.genmed')->attr('href');

            $items[$url] = $item;
        }

        return ($items);
    }

    protected function createItem($url, \phpQueryObject $element, \phpQueryObject $page)
    {
        $item  = new Item($this);
        $topic = pqElement($page->find('.forumline:last .row1:first > .row1:last'));

        // Page torrent
        $item->setPage($url);

        // Category torrent
        $category = trim($page->find('table a.nav[href^="viewforum"]:first')->text());
        $item->setCategory((!empty($category) ? $category : 'unknown'));

        // Title torrent
        $title = trim($page->find('a.maintitle')->text());
        $item->setTitle((!empty($title) ? $title : 'unknown'));

        // Url download torrent
        $download = $topic->find('.btTbl .gensmall .genmed a:first')->attr('href');
        $item->setFetch((!empty($download) ? (self::SITE_PREFIX . '/' . $download) : 'unknown'));

        // Torrent size
        $item->setSize((float)$element->find('td.gensmall:not(:last) u')->text());

        // Torrent count seeds
        $item->setSeeds((int)trim($element->find('.seedmed b')->text()));

        // Torrent count peers
        $item->setPeers((int)trim($element->find('.leechmed b')->text()));

        // Date created torrent
        $item->setDate((new \DateTime())->setTimestamp((int)$element->find('td:last u')->text()));

        yield $item;
    }

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
