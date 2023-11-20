<?php declare(strict_types=1);

use Digua\Env;

return [
    'host'   => Env::get('REDIS_HOST', 'localhost'),
    'port'   => Env::get('REDIS_PORT', 6379),
    'pass'   => Env::get('REDIS_PASSWORD', null),
    'expire' => Env::get('CACHE_EXPIRE', 86400),
];