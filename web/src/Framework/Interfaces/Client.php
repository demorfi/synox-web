<?php

namespace Framework\Interfaces;

interface Client
{
    /**
     * Set url.
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * Add query.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addQuery($name, $value);

    /**
     * Add field.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addField($name, $value);

    /**
     * Set client option.
     *
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function setOption($name, $value);

    /**
     * Get client option.
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name);

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get response.
     *
     * @return string
     */
    public function getResponse();

    /**
     * Send request.
     *
     * @return void
     */
    public function send();

    /**
     * Clean request.
     *
     * @return void
     */
    public function clean();
}
