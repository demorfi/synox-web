<?php declare(strict_types=1);

use Digua\Env;

return [
    'private' => sprintf(
        'tcp://%s:%d',
        Env::get('WORKER_PRIVATE_HOST', '0.0.0.0', 'string'),
        Env::get('WORKER_PRIVATE_PORT', 1234, 'int')
    ),

    'public' => sprintf(
        'websocket://%s:%d',
        Env::get('WORKER_PUBLIC_HOST', '0.0.0.0', 'string'),
        Env::get('WORKER_PUBLIC_PORT', 2346, 'int')
    ),

    'broadcast' => sprintf(
        'websocket://%s:%d',
        Env::get('WORKER_BROADCAST_HOST', Env::get('WORKER_PUBLIC_HOST', '0.0.0.0', 'string'), 'string'),
        Env::get('WORKER_BROADCAST_PORT', Env::get('WORKER_PUBLIC_PORT', 2346, 'int'), 'int')
    ),

    'ssl' => [
        'use'    => Env::get('WORKER_USE_SSL', false, 'bool'),
        'cert'   => Env::get('WORKER_SSL_CERT_PATH', null, 'string'),
        'key'    => Env::get('WORKER_SSL_KEY_PATH', null, 'string'),
        'verify' => Env::get('WORKER_SSL_VERIFY', false, 'bool')
    ]
];