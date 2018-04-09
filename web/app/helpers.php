<?php

use Framework\Config;
use Framework\Template;

if (!function_exists('tpl')) {

    /**
     * Get template instance.
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
     * Get config instance.
     *
     * @param $name
     * @return Config
     */
    function config($name)
    {
        return (new Config($name));
    }
}

if (!function_exists('pqInstance')) {

    /**
     * phpQuery is a server-side, chainable, CSS3 selector driven
     * Document Object Model (DOM) API based on jQuery JavaScript Library.
     *
     * @param string $markup
     * @param string $contentType
     * @return phpQueryObject
     */
    function pqInstance($markup = null, $contentType = null)
    {
        if (!class_exists('phpQuery', false)) {
            include_once(APP_PATH . '/Classes/Vendors/phpQuery/phpQuery.php');
        }
        return (phpQuery::newDocument($markup, $contentType));
    }
}

if (!function_exists('pqElement')) {

    /**
     * phpQuery is a server-side, chainable, CSS3 selector driven
     * Document Object Model (DOM) API based on jQuery JavaScript Library.
     *
     * @param mixed $element
     * @return phpQueryObject
     */
    function pqElement($element)
    {
        if (!class_exists('phpQuery', false)) {
            include_once(APP_PATH . '/Classes/Vendors/phpQuery/phpQuery.php');
        }
        return (phpQuery::pq($element));
    }
}

if (!function_exists('deUnicode')) {

    /**
     * Unicode to UTF-8.
     *
     * @param string $string
     * @return string
     */
    function deUnicode($string)
    {
        return (preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            function ($match) {
                return (mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE'));
            },
            $string
        ));
    }
}
