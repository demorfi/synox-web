<?php

namespace Framework\Request;

use Framework\Abstracts\Data as _Data;

class Data extends _Data
{
    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->array = filter_input_array(INPUT_POST);
    }
}