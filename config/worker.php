<?php declare(strict_types=1);

use Digua\Env;

return [
    'private' => sprintf(
        'tcp://%s:%d',
        Env::get('WORKER_PRIVATE_HOST', '0.0.0.0'),
        Env::get('WORKER_PRIVATE_PORT', 1234)
    ),

    'public' => sprintf(
        'websocket://%s:%d',
        Env::get('WORKER_PUBLIC_HOST', '0.0.0.0'),
        Env::get('WORKER_PUBLIC_PORT', 2346)
    ),

    'broadcast' => sprintf(
        'websocket://%s:%d',
        Env::get('WORKER_BROADCAST_HOST', Env::get('WORKER_PUBLIC_HOST', '0.0.0.0')),
        Env::get('WORKER_BROADCAST_PORT', Env::get('WORKER_PUBLIC_PORT', 2346))
    ),

    'ssl' => [
        'use'    => Env::get('WORKER_USE_SSL', false),
        'cert'   => Env::get('WORKER_SSL_CERT_PATH', null),
        'key'    => Env::get('WORKER_SSL_KEY_PATH', null),
        'verify' => Env::get('WORKER_SSL_VERIFY', false)
    ]
];