<?php

namespace Framework\Components\Client;

use Framework\Interfaces\Client;

class Curl implements Client
{
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535 (KHTML, like Gecko) Chrome/14 Safari/535';

    const COOKIES_PATH = APP_PATH . '/Storage/';

    const COOKIE_EXTENSION = '.cookie';

    protected $instance;

    public function __construct()
    {
        $this->instance = new \stdClass;

        $this->instance->curl     = curl_init();
        $this->instance->response = null;
        $this->instance->url      = null;
        $this->instance->fields   = [];
        $this->instance->query    = [];

        curl_setopt($this->instance->curl, CURLOPT_HEADER, false);
        curl_setopt($this->instance->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->instance->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->instance->curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->instance->curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->instance->curl, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT);
        curl_setopt($this->instance->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->instance->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        if (is_resource($this->instance->curl)) {
            curl_close($this->instance->curl);
        }
    }

    public function setUrl($url)
    {
        $this->instance->url = $url;
    }

    public function addQuery($name, $value)
    {
        $this->instance->query[$name] = $value;
    }

    public function addField($name, $value)
    {
        $this->instance->fields[$name] = $value;
    }

    public function setOption($name, $value)
    {
        curl_setopt($this->instance->curl, $name, $value);
    }

    public function getOption($name)
    {
        return (curl_getinfo($this->instance->curl, $name));
    }

    public function useCookie($name)
    {
        $filePath = self::COOKIES_PATH . $this->cleanFileName($name) . self::COOKIE_EXTENSION;
        curl_setopt($this->instance->curl, CURLOPT_COOKIEJAR, $filePath);
        curl_setopt($this->instance->curl, CURLOPT_COOKIEFILE, $filePath);
    }

    protected function cleanFileName($fileName)
    {
        return (strtr(
            mb_convert_encoding($fileName, 'ASCII'),
            ' ,;:?*#!§$%&/(){}<>=`´|\\\'"',
            '____________________________'
        ));
    }

    public function getUrl()
    {
        $url = $this->instance->url;
        if (!empty($this->instance->query)) {
            $url .= ((strpos($url, '?') === false ? '?' : '&') . http_build_query($this->instance->query));
        }
        return ($url);
    }

    public function getResponse()
    {
        return ($this->instance->response);
    }

    public function send()
    {
        curl_setopt($this->instance->curl, CURLOPT_URL, $this->getUrl());

        if (!empty($this->instance->fields)) {
            curl_setopt($this->instance->curl, CURLOPT_POST, true);
            curl_setopt($this->instance->curl, CURLOPT_POSTFIELDS, http_build_query($this->instance->fields));
        }

        $this->instance->response = curl_exec($this->instance->curl);
    }

    public function clean()
    {
        $this->__construct();
    }
}
