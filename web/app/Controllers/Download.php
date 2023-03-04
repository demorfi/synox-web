<?php declare(strict_types=1);

namespace App\Controllers;

use App\Components\Storage\Journal as JournalStorage;
use App\Repositories\Packages as PackagesRepository;
use App\Package\Download\{Filter as FilterDownload, Torrent};
use App\Package\PackageDispatcher;
use App\Enums\PackageType;
use Digua\Interfaces\Request as RequestInterface;
use Digua\{LateEvent, Template, Response, Helper};
use Digua\Controllers\Base as BaseController;
use Digua\Exceptions\Path as PathException;
use Exception;

class Download extends BaseController
{
    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        parent::__construct($request);
        LateEvent::subscribe(
            PackageDispatcher::class,
            fn($message) => JournalStorage::staticPush($message)
        );
    }

    /**
     * Default action.
     *
     * @return Template
     * @throws PathException
     */
    public function defaultAction(): Template
    {
        $title    = 'Download';
        $filters  = FilterDownload::uses();
        $packages = PackagesRepository::getInstance()->getPackages()
            ->getByType(PackageType::Download)
            ->getByEnabled();
        return $this->render('download', compact('title', 'packages', 'filters'))
            ->javascripts(['packages/download']);
    }

    /**
     * Search action.
     *
     * @return array
     */
    public function searchAction(): array
    {
        [$query, $package, $filters, $hash] = $this->dataRequest()->post()
            ->only('query', 'package', 'filters', 'hash');

        if (empty($query)) {
            return ['success' => false, 'error' => 'Empty search query!'];
        }

        $dispatcher = new PackageDispatcher($hash);
        if (!$dispatcher->hasHash()) {
            return ['success' => true, 'hash' => $dispatcher->makePackageHash()];
        }

        if (!$dispatcher->usePackages(PackageType::Download, [$package])) {
            return ['success' => false, 'error' => 'Download packages not found or not enabled!'];
        }

        try {
            $dispatcher->makeNewSearchQuery((string)$query, new FilterDownload((array)$filters));
            return ['success' => $dispatcher->search()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Results search action.
     *
     * @return array
     * @throws PathException
     */
    public function resultsAction(): array
    {
        [$hash, $limit] = $this->dataRequest()->post()
            ->only('hash', 'limit');

        $limit  ??= Helper::config('app')->get('results-limit', 25);
        $chunks = [];

        try {
            $isEnd = (new PackageDispatcher($hash))->result($chunks, (int)$limit) === -1;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return compact('chunks', 'isEnd');
    }

    /**
     * Fetch torrent action.
     *
     * @return array
     */
    public function fetchAction(): array
    {
        [$id, $url] = $this->dataRequest()->post()
            ->only('id', 'url');

        if (empty($id) || empty($url)) {
            return ['success' => false, 'error' => 'Empty fetch request!'];
        }

        $dispatcher = new PackageDispatcher();
        if (!$dispatcher->usePackages(PackageType::Download, [$id])) {
            return ['success' => false, 'error' => 'Download package not found or not enabled!'];
        }

        try {
            $file = $dispatcher->fetch($id, urldecode($url));
            if ($file->isAvailable()) {
                return ['success' => true, 'file' => $file];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return ['success' => false, 'error' => 'Unable to get file!'];
    }

    /**
     * Download torrent action.
     *
     * @return Response|array
     */
    public function downloadAction(): Response|array
    {
        $name = $this->dataRequest()->query()->get('name');
        if (empty($name)) {
            return ['success' => false, 'error' => 'Empty download request!'];
        }

        $file = (new Torrent)->open($name);
        if ($file->isAvailable()) {
            return (new Response)->redirectTo($file->getFileUrl());
        }

        return ['success' => false, 'error' => 'Unable to get file!'];
    }
}
