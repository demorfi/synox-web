<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Components\{Helper, Storage\Journal};
use App\Package\{Search\Dispatcher, Search\Filter};
use Digua\{LateEvent, Response};
use Digua\Enums\Headers;
use Digua\Exceptions\{Abort as AbortException, Base as BaseException};

class Search extends Base
{
    /**
     * @inheritdoc
     */
    protected function init(): void
    {
        parent::init();
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
            $query   = htmlspecialchars_decode($request->getFixedTypeValue('query', 'string'));

            if (empty($query) || strlen($query) <= 3) {
                $this->throwAbort(Headers::FAILED_DEPENDENCY, 'Empty or short search query!');
            }

            $params = $request->getFixedTypeValue('params', 'array');
            $config = Helper::config('worker')->collection();
            $filter = $request->callWrap(static function ($request) {
                $filter = new Filter($request->getFixedTypeValue('filters', 'array'));
                $request->callWrapIfTrue(
                    fn($request) => $filter->loadProfile($request->getFixedTypeValue('profile', 'string')),
                    $request->has('profile')
                );
                return !$filter->collection()->isEmpty() ? $filter : null;
            });

            $dispatcher = new Dispatcher();
            return $this->response([
                'token' => $dispatcher->makeNewSearchQuery($query, $filter, $params),
                'host'  => $config->replaceValue('broadcast', static function ($value) use ($config) {
                    $usesSsl = $config->collapse('ssl')->getFixedTypeValue('use', 'bool', false);
                    return str_ireplace('websocket:', $usesSsl ? 'wss:' : 'ws:', $value);
                })->get('broadcast'),
                'limit' => Helper::config('app')->get('limitPerPackage')
            ], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}