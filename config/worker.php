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
    )
];