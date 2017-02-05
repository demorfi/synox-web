<?php

namespace Framework;

class Route
{
    /**
     * Request.
     *
     * @var Request
     */
    private $request;

    /**
     * Route constructor.
     */
    public function __construct()
    {
        $this->request = new Request();

        try {
            $name   = '\Controllers\\' . ucfirst($this->request->getQuery()->getName());
            $action = $this->request->getQuery()->getAction() . 'Action';

            if (!class_exists($name, true)) {
                throw new \Exception($name . ' - controller not found');
            }

            $controller = new $name($this->request, new Response);
            if (!method_exists($controller, $action)) {
                throw new \Exception($name . '->' . $action . ' - action not found');
            }

            call_user_func([$controller, $action]);
        } catch (\Exception $e) {
            print ($e->getMessage());
        }
    }
}
