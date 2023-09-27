<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Components\Storage\Journal;
use App\Package\Dispatcher;
use App\Enums\ItemType;
use Digua\{LateEvent, Response};
use Digua\Enums\Headers;
use Digua\Controllers\Resource as ResourceController;
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Interfaces\Request;
use Digua\Exceptions\{
    Abort as AbortException,
    Base as BaseException
};

class Content extends ResourceController
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
     * @param string $packageId
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('packageId')]
    public function postFetchAction(string $packageId): Response
    {
        try {
            $dispatcher = new Dispatcher();
            if (!$dispatcher->usePackages(onlyPackages: [$packageId])) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY, 'Package not found or not enabled!');
            }

            $fetchId = $this->dataRequest()->post()->getFixedTypeValue('fetchId', 'string');
            if (empty($fetchId)) {
                $this->throwAbort(Headers::FAILED_DEPENDENCY, 'Empty fetchId request!');
            }

            $content = $dispatcher->fetch($packageId, urldecode($fetchId));
            if (!$content->isAvailable()) {
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
        $content = ItemType::tryName($type)?->contentType()->make();
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
