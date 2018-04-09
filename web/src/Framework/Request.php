<?php

namespace Framework;

use Framework\Request\Data;
use Framework\Request\Query;

class Request
{
    /**
     * Query instance.
     *
     * @var Query
     */
    private $query;

    /**
     * Data instance.
     *
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
     * Get data instance.
     *
     * @return Data
     */
    public function getData()
    {
        return ($this->data);
    }

    /**
     * Get query instance.
     *
     * @return Query
     */
    public function getQuery()
    {
        return ($this->query);
    }
}
