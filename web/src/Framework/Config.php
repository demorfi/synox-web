<?php

namespace Framework;

class Config extends Abstracts\Data
{
    /**
     * Path to config.
     *
     * @var string
     */
    const PATH = APP_PATH . '/Configs/';

    /**
     * Config constructor.
     *
     * @param string $name Config name
     */
    public function __construct($name)
    {
        $this->array = require(static::PATH . $name . PHP_EXT);
    }
}
