<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Components\{Helper, Storage\Journal};
use App\Package\{Dispatcher, Filter};
use Digua\{Response, LateEvent};
use Digua\Enums\Headers;
use Digua\Controllers\Resource as ResourceController;
use Digua\Interfaces\Request;
use Digua\Exceptions\{
    Abort as AbortException,
    Base as BaseException
};

class Search extends ResourceController
{
    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        LateEvent::subscribe(Dispatcher::class, fn($message) => Journal::staticPush($message));
    }

    /**
     * @return Response
     * @throws AbortException
     */
    public function postStartAction(): Response
    {
        try {
            $request = $this->dataRequest()->post();
            $query   = $request->getFixedTypeValue('query', 'string');
            $filters = $request->getFixedTypeValue('filters', 'array');

            if (empty($query) || strlen($query) <= 3) {
                $this->throwAbort(Headers::FAILED_DEPENDENCY, 'Empty or short search query!');
            }

            $dispatcher = new Dispatcher();
            if (!$dispatcher->usePackages(onlyPackages: $filters['packages'] ?? [])) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY, 'Packages not found or not enabled!');
            }

            return $this->response([
                'hash'  => $dispatcher->makeNewSearchQuery($query, new Filter($filters)),
                'host'  => Helper::config('worker')->get('public'),
                'limit' => Helper::config('app')->get('limitPerPackage')
            ], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}
