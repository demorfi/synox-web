<?php

namespace Framework\Traits;

use Framework\Interfaces\Client as _Client;

trait Client
{
    /**
     * Send POST request.
     *
     * @param _Client $client
     * @param string  $url
     * @param array   $fields
     * @return string
     */
    protected function sendPost(_Client $client, $url, $fields = [])
    {
        $client->setUrl($url);

        foreach ($fields as $name => $value) {
            $client->addField($name, $value);
        }

        $client->send();
        return ($client->getResponse());
    }

    /**
     * Send GET request.
     *
     * @param _Client $client
     * @param string  $url
     * @param array   $fields
     * @return string
     */
    protected function sendGet(_Client $client, $url, $fields = [])
    {
        $client->setUrl($url);

        foreach ($fields as $name => $value) {
            $client->addQuery($name, $value);
        }

        $client->send();
        return ($client->getResponse());
    }
}
