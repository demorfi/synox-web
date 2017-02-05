<?php

namespace Framework\Abstracts;

use Framework\Request;
use Framework\Response;

abstract class Controller
{
    /**
     * Request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Response.
     *
     * @var Response
     */
    protected $response;

    /**
     * Controller constructor.
     *
     * @param Response $response
     * @param Request  $request
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }
}
