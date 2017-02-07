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

if (!function_exists('phpQuery')) {

    /**
     * phpQuery is a server-side, chainable, CSS3 selector driven
     * Document Object Model (DOM) API based on jQuery JavaScript Library.
     *
     * @param string $markup
     * @param string $contentType
     * @return phpQueryObject
     */
    function phpQuery($markup = null, $contentType = null)
    {
        if (!class_exists('phpQuery', false)) {
            include_once(APP_PATH . '/Classes/Vendors/phpQuery/phpQuery.php');
        }
        return (phpQuery::newDocument($markup, $contentType));
    }
}
