<?php

use Framework\Storage;

$system = Storage::load('system');
if ($system->get('api-key', false) === false) {
    $system->__set('api-key', md5(microtime()));
}

return ([
    'api-key' => $system->get('api-key')
]);

