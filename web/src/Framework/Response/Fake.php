<?php

namespace Framework\Response;

use Framework\Response;

class Fake extends Response
{
    /**
     * Location.
     *
     * @var string
     */
    public $location;

    /**
     * Data.
     *
     * @var mixed
     */
    public $json;

    /**
     * @inheritdoc
     */
    public function location($url)
    {
        $this->location = $url;
        return (null);
    }

    /**
     * @inheritdoc
     */
    public function json($data)
    {
        $this->json = $data;
        return (null);
    }
}
