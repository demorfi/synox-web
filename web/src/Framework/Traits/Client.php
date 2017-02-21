<?php

namespace Framework\Traits;

use Framework\Interfaces\Client as _Client;

trait Client
{
    protected function sendPost(_Client $client, $url, $fields = [])
    {
        $client->setUrl($url);

        foreach ($fields as $name => $value) {
            $client->addField($name, $value);
        }

        $client->send();
        return ($client->getResponse());
    }

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
