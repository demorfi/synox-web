<?php

namespace Controllers;

use Classes\Packages;
use Classes\Packages\Download\Stack;
use Classes\Packages\Download\Torrent;
use Framework\Abstracts\Controller;
use Framework\Components\Journal\Storage as StorageJournal;

class Downloads extends Controller
{
    /**
     * Default action.
     *
     * @return mixed
     */
    public function defaultAction()
    {
        $title = 'Downloads';
        return (tpl()->render('downloads', compact('title'))
            ->javascripts(['packages/downloads']));
    }

    /**
     * Search action.
     *
     * @return mixed
     */
    public function searchAction()
    {
        $packages = Packages::getInstance()->getPackages()->getByType('Downloads');
        if (!$packages) {
            return ($this->response->json(['success' => false, 'error' => 'Download packages not found!']));
        }

        if (!($packages = $packages->getByEnabled())->count()) {
            return ($this->response->json(['success' => false, 'error' => 'Download packages not enabled!']));
        }

        if (!$this->request->getData()->has('name')) {
            return ($this->response->json(['success' => false, 'error' => 'Empty search query!']));
        }

        $name = $this->request->getData()->get('name');
        $hash = $this->request->getData()->get('hash');

        $stack = new Stack($hash);
        if (empty($hash)) {
            return ($this->response->json(['success' => true, 'hash' => $stack->getHash()]));
        }

        $journal = StorageJournal::getInstance();
        $journal->push('download: search (' . $name . ') running');
        foreach ($packages as $package) {
            try {

                /**
                 * @var $package \Classes\Interfaces\Download
                 * @var $stack   \Framework\Memory
                 */
                $journal->push('download: search (' . $name . ') through the package ' . $package->getName());

                $size = $stack->size();
                $package->searchByName($name, $stack);

                $pkgSize = abs($size - $stack->size());
                $size += $pkgSize;
                $journal->push('download: found ' . $pkgSize . ' records of total ' . $size . ' records');
            } catch (\Exception $e) {
                $journal->push('download: ' . $e->getMessage());
            }
        }

        $journal->push('download: search (' . $name . ') finished');
        $stack->setEndFlag();

        return ($this->response->json(['success' => true]));
    }

    /**
     * Results search action.
     *
     * @return mixed
     */
    public function resultsAction()
    {
        if (!$this->request->getData()->has('hash')) {
            return ($this->response->json(['success' => false, 'error' => 'Stack hash not found!']));
        }

        $hash  = $this->request->getData()->get('hash');
        $limit = $this->request->getData()->get('limit', 1);

        /* @var $stack \Framework\Memory */
        $stack     = new Stack($hash);
        $chunks    = [];
        $sizeChunk = ($limit == -1
            ? $stack->size()
            : $limit);

        try {
            $index = 0;

            /* @var $stack \Framework\Memory */
            foreach ($stack->read() as $item) {
                if ($stack->isEndFlag($item)) {
                    $stack->free();
                    return ($this->response->json(['chunks' => $chunks, 'isEnd' => true]));
                }

                array_push($chunks, $item);
                $index++;

                if ($index >= $sizeChunk) {
                    break;
                }
            }
        } catch (\Exception $e) {
            return ($this->response->json(['success' => false, 'error' => $e->getMessage()]));
        }

        return ($this->response->json(compact('chunks')));
    }

    /**
     * Fetch torrent action.
     *
     * @return mixed
     */
    public function fetchAction()
    {
        if (!$this->request->getData()->has('id') || !$this->request->getData()->has('url')) {
            return ($this->response->json(['success' => false, 'error' => 'Empty fetch request!']));
        }

        /* @var $package \Classes\Interfaces\Download */
        $package = Packages::getInstance()->getPackages()->find($this->request->getData()->get('id'));
        if (!$package) {
            return ($this->response->json(['success' => false, 'error' => 'Download package not found!']));
        }

        $url     = urldecode($this->request->getData()->get('url'));
        $journal = StorageJournal::getInstance();
        $torrent = new Torrent();

        try {
            $package->fetch($url, $torrent);
        } catch (\Exception $e) {
            $journal->push('download: ' . $e->getMessage());
        }

        if ($torrent->isAvailable()) {
            $journal->push('download: fetch available (' . $url . ') through the package ' . $package->getName());
            return ($this->response->json(['success' => true, 'file' => $torrent]));
        }

        $journal->push('download: fetch unable (' . $url . ') through the package ' . $package->getName());
        return ($this->response->json(['success' => false, 'error' => 'Unable to get file!']));
    }

    /**
     * Download torrent action.
     *
     * @return mixed
     */
    public function downloadAction()
    {
        if (!$this->request->getQuery()->has('name')) {
            return ($this->response->json(['success' => false, 'error' => 'Empty download request!']));
        }

        $torrent = (new Torrent())->open($this->request->getQuery()->get('name'));
        if ($torrent->isAvailable()) {
            return ($this->response->location($torrent->getFileUrl()));
        }

        return ($this->response->json(['success' => false, 'error' => 'Unable to get file!']));
    }
}
