<?php declare(strict_types=1);

namespace App\Controllers;

use App\Components\Storage\Journal as JournalStorage;
use App\Repositories\Packages as PackagesRepository;
use App\Package\Lyrics\Filter as FilterLyrics;
use App\Package\PackageDispatcher;
use App\Enums\PackageType;
use Digua\Interfaces\Request as RequestInterface;
use Digua\{LateEvent, Template, Helper};
use Digua\Controllers\Base as BaseController;
use Digua\Exceptions\Path as PathException;
use Exception;

class Lyrics extends BaseController
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
        $title    = 'Lyrics';
        $filters  = FilterLyrics::uses();
        $packages = PackagesRepository::getInstance()->getPackages()
            ->getByType(PackageType::Lyrics)
            ->getByEnabled();
        return ($this->render('lyrics', compact('title', 'packages', 'filters'))
            ->javascripts(['packages/lyrics']));
    }

    /**
     * Search action.
     *
     * @return array
     */
    public function searchAction(): array
    {
        [$query, $hash, $package] = $this->dataRequest()->post()
            ->only('query', 'hash', 'package');

        if (empty($query)) {
            return ['success' => false, 'error' => 'Empty search query!'];
        }

        $dispatcher = new PackageDispatcher($hash);
        if (!$dispatcher->hasHash()) {
            return ['success' => true, 'hash' => $dispatcher->makePackageHash()];
        }

        if (!$dispatcher->usePackages(PackageType::Lyrics, [$package])) {
            return ['success' => false, 'error' => 'Lyrics packages not found or not enabled!'];
        }

        try {
            $dispatcher->makeNewSearchQuery((string)$query);
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

        $limit  ??= Helper::config('app')->get('results-limit', 10);
        $chunks = [];

        try {
            $isEnd = (new PackageDispatcher($hash))->result($chunks, (int)$limit) === -1;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return compact('chunks', 'isEnd');
    }

    /**
     * Fetch lyric action.
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
        if (!$dispatcher->usePackages(PackageType::Lyrics, [$id])) {
            return ['success' => false, 'error' => 'Lyrics package not found or not enabled!'];
        }

        try {
            $content = $dispatcher->fetch($id, urldecode($url));
            if ($content->isAvailable()) {
                return ['success' => true, 'data' => $content];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return ['success' => false, 'error' => 'Unable to get content!'];
    }
}
