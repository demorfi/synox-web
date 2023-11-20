<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Components\Storage\Journal as JournalStorage;
use Digua\Response;
use Digua\Enums\Headers;
use Digua\Exceptions\{Base as BaseException, Abort as AbortException};

class Journal extends Base
{
    /**
     * @return array
     */
    public function getDefaultAction(): array
    {
        return JournalStorage::getInstance()->toArray();
    }

    /**
     * @return array
     */
    public function getSizeAction(): array
    {
        return ['size' => JournalStorage::getInstance()->size()];
    }

    /**
     * @return Response
     * @throws AbortException
     */
    public function deleteDefaultAction(): Response
    {
        try {
            JournalStorage::getInstance()->flush();
            return $this->response(['success' => true]);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}