<?php

namespace Framework;

use Framework\Request\Data;
use Framework\Request\Query;

class Request
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @var Data
     */
    private $data;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->query = new Query();
        $this->data  = new Data();
    }

    /**
     * @return Data
     */
    public function getData()
    {
        return ($this->data);
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return ($this->query);
    }
}