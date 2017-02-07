<?php

namespace Framework\Interfaces;

interface Client
{
    public function setUrl($url);

    public function addQuery($name, $value);

    public function addField($name, $value);

    public function setOption($name, $value);

    public function getOption($name);

    public function getUrl();

    public function getResponse();

    public function send();

    public function clean();
}
