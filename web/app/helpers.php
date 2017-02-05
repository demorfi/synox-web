<?php

use Framework\Config;
use Framework\Template;

if (!function_exists('tpl')) {

    /**
     * Is dnsmasq service disabled.
     *
     * @return Template
     */
    function tpl()
    {
        return (new Template);
    }
}

if (!function_exists('config')) {

    /**
     * Is dnsmasq service disabled.
     *
     * @param $name
     * @return Config
     */
    function config($name)
    {
        return (new Config($name));
    }
}