<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Components\Storage\Journal;
use App\Package\Search\{Dispatcher, Enums\Subtype};
use Digua\{LateEvent, Response};
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Enums\Headers;
use Digua\Exceptions\{Abort as AbortException, Base as BaseException};

class Content extends Base
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
     * @param string $packageId
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('packageId')]
    public function postFetchAction(string $packageId): Response
    {
        try {
            $request = $this->dataRequest()->post();
            $fetchId = htmlspecialchars_decode($request->getFixedTypeValue('fetchId', 'string'));
            $params  = $request->getFixedTypeValue('params', 'array');

            if (empty($fetchId)) {
                $this->throwAbort(Headers::FAILED_DEPENDENCY, 'Empty fetchId request!');
            }

            $dispatcher = new Dispatcher();
            $content = $dispatcher->fetch($packageId, $fetchId, $params);
            if (!$content?->isAvailable()) {
                $this->throwAbort(Headers::BAD_REQUEST, 'Unable to get content!');
            }

            return $this->response($content, Headers::CREATED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('name', 'type')]
    public function getDownloadAction(string $name, string $type): Response
    {
        $content = Subtype::tryName($type)?->makeContent();
        if (empty($content)) {
            $this->throwAbort(Headers::UNPROCESSABLE_ENTITY, 'Invalid content type!');
        }

        $content->open($name);
        if (!$content->isAvailable()) {
            $this->throwAbort(Headers::UNPROCESSABLE_ENTITY, 'Unable to get file!');
        }

        return (new Response)->redirectTo($content->getPath());
    }
}