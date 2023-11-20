<?php declare(strict_types=1);

namespace App\Controllers;

use Digua\Controllers\Error as ErrorBase;
use Digua\Enums\Headers;
use Digua\Response;

class Error extends ErrorBase
{
    /**
     * @inheritdoc
     */
    public function defaultAction(): Response
    {
        return Response::create(['error' => $this->message])
            ->addHttpHeader(Headers::from($this->code));
    }
}