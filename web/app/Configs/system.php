<?php

use Framework\Storage;

// Crutch for synology au (lyrics) plugins
if (!function_exists('crutchAuPlugins')) {
    function crutchAuPlugins($key)
    {
        $data = ['host' => Framework\Request\Query::getHost(), 'key' => $key];
        file_put_contents('/tmp/synox.json', json_encode($data));
        chmod('/tmp/synox.json', 0777);
    }
}

$system = Storage::load('system');
if ($system->get('api-key', false) === false) {
    $system->__set('api-key', md5(microtime()));
    crutchAuPlugins($system->get('api-key'));
}

// Crutch for synology au (lyrics) plugins
if (!is_readable('/tmp/synox.json')) {
    crutchAuPlugins($system->get('api-key'));
}

return ([
    'api-key' => $system->get('api-key')
]);

