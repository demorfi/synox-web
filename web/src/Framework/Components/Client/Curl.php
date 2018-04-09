<?php

namespace Framework\Components\Client;

use Framework\Interfaces\Client;

class Curl implements Client
{
    /**
     * User agent.
     *
     * @var string
     */
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535 (KHTML, like Gecko) Chrome/14 Safari/535';

    /**
     * Path to storage.
     *
     * @var string
     */
    const COOKIES_PATH = APP_PATH . '/Storage/';

    /**
     * Cookie extension.
     *
     * @var string
     */
    const COOKIE_EXTENSION = '.cookie';

    /**
     * Object instance.
     *
     * @var \stdClass
     */
    protected $instance;

    /**
     * Curl constructor.
     */
    public function __construct()
    {
        $this->instance = new \stdClass;

        $this->instance->curl     = curl_init();
        $this->instance->response = null;
        $this->instance->url      = null;
        $this->instance->fields   = [];
        $this->instance->query    = [];

        // Set default curl options
        curl_setopt($this->instance->curl, CURLOPT_HEADER, false);
        curl_setopt($this->instance->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->instance->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->instance->curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->instance->curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->instance->curl, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT);
        curl_setopt($this->instance->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->instance->curl, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        if (is_resource($this->instance->curl)) {
            curl_close($this->instance->curl);
        }
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->instance->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function addQuery($name, $value)
    {
        $this->instance->query[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function addField($name, $value)
    {
        $this->instance->fields[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setOption($name, $value)
    {
        curl_setopt($this->instance->curl, $name, $value);
    }

    /**
     * @inheritdoc
     */
    public function getOption($name)
    {
        return (curl_getinfo($this->instance->curl, $name));
    }

    /**
     * Use cookie.
     *
     * @param string $name
     * @return void
     */
    public function useCookie($name)
    {
        $filePath = self::COOKIES_PATH . $this->cleanFileName($name) . self::COOKIE_EXTENSION;
        curl_setopt($this->instance->curl, CURLOPT_COOKIEJAR, $filePath);
        curl_setopt($this->instance->curl, CURLOPT_COOKIEFILE, $filePath);
    }

    /**
     * Get safe name file.
     *
     * @param string $fileName
     * @return string
     */
    protected function cleanFileName($fileName)
    {
        return (strtr(
            mb_convert_encoding($fileName, 'ASCII'),
            ' ,;:?*#!§$%&/(){}<>=`´|\\\'"',
            '____________________________'
        ));
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        $url = $this->instance->url;
        if (!empty($this->instance->query)) {
            $url .= ((strpos($url, '?') === false ? '?' : '&') . http_build_query($this->instance->query));
        }
        return ($url);
    }

    /**
     * @inheritdoc
     */
    public function getResponse()
    {
        return ($this->instance->response);
    }

    /**
     * @inheritdoc
     */
    public function send()
    {
        curl_setopt($this->instance->curl, CURLOPT_URL, str_replace(' ', '%20', $this->getUrl()));

        if (!empty($this->instance->fields)) {
            curl_setopt($this->instance->curl, CURLOPT_POST, true);
            curl_setopt($this->instance->curl, CURLOPT_POSTFIELDS, http_build_query($this->instance->fields));
        }

        $this->instance->response = curl_exec($this->instance->curl);
    }

    /**
     * @inheritdoc
     */
    public function clean()
    {
        $this->__construct();
    }
}
