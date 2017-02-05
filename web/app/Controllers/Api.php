<?php

namespace Controllers;

use Classes\Packages\Download\Torrent;
use Framework\Abstracts\Controller;
use Framework\Response\Fake as FakeResponse;
use Framework\Storage;

class Api extends Controller
{
    /**
     * Default action.
     *
     * @return mixed
     */
    public function defaultAction()
    {
        return ($this->response->json(['success' => false, 'error' => 'API method not found!']));
    }

    /**
     * Has query API key.
     *
     * @return bool
     */
    protected function hasApiKey()
    {
        $system = Storage::load('system');
        return ($this->request->getQuery()->get('api-key') == $system->get('api-key'));
    }

    /**
     * Download action.
     *
     * @return mixed
     */
    public function downloadAction()
    {
        if (!$this->hasApiKey()) {
            return ($this->response->json(['success' => false, 'error' => 'API key is wrong!']));
        }

        $fakeResponse = new FakeResponse();
        $download     = new Downloads($this->request, $fakeResponse);

        $type = $this->request->getQuery()->get('type');
        switch ($type) {
            case ('search'):
                $this->request->getData()->__set('name', $this->request->getQuery()->get('name'));
                $download->searchAction();

                if (isset($fakeResponse->json['hash'])) {
                    $this->request->getData()->__set('hash', $fakeResponse->json['hash']);

                    $download->searchAction();
                    if (isset($fakeResponse->json['success']) && $fakeResponse->json['success'] === true) {
                        $this->request->getData()->__set('limit', -1);
                        $download->resultsAction();
                    }
                }
                return ($this->response->json($fakeResponse->json));

            case ('fetch'):
                $this->request->getData()->__set('id', $this->request->getQuery()->get('id'));
                $this->request->getData()->__set('url', $this->request->getQuery()->get('url'));

                $download->fetchAction();
                if (isset($fakeResponse->json['file'])) {
                    /* @var $file Torrent */
                    $file = $fakeResponse->json['file'];
                    return ($this->response->json(
                        [
                            'success' => true,
                            'file'    => $this->request->getQuery()->getHost() . $file->getFileUrl()
                        ]
                    ));
                }
                return ($this->response->json($fakeResponse->json));

            default:
                return ($this->response->json(['success' => false, 'error' => 'API method not found!']));
        }
    }

    /**
     * Lyrics action.
     *
     * @return mixed
     */
    public function lyricsAction()
    {
        if (!$this->hasApiKey()) {
            return ($this->response->json(['success' => false, 'error' => 'API key is wrong!']));
        }

        $fakeResponse = new FakeResponse();
        $download     = new Lyrics($this->request, $fakeResponse);

        $type = $this->request->getQuery()->get('type');
        switch ($type) {
            case ('search'):
                $this->request->getData()->__set('name', $this->request->getQuery()->get('name'));
                $download->searchAction();

                if (isset($fakeResponse->json['hash'])) {
                    $this->request->getData()->__set('hash', $fakeResponse->json['hash']);

                    $download->searchAction();
                    if (isset($fakeResponse->json['success']) && $fakeResponse->json['success'] === true) {
                        $this->request->getData()->__set('limit', -1);
                        $download->resultsAction();
                    }
                }
                return ($this->response->json($fakeResponse->json));

            case ('fetch'):
                $this->request->getData()->__set('id', $this->request->getQuery()->get('id'));
                $this->request->getData()->__set('url', $this->request->getQuery()->get('url'));

                $download->fetchAction();
                return ($this->response->json($fakeResponse->json));

            default:
                return ($this->response->json(['success' => false, 'error' => 'API method not found!']));
        }
    }
}
