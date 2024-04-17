<?php declare(strict_types=1);

use Digua\Env;

return [
    'host'   => Env::get('REDIS_HOST', 'localhost', 'string'),
    'port'   => Env::get('REDIS_PORT', 6379, 'int'),
    'pass'   => Env::get('REDIS_PASSWORD', null, 'string'),
    'expire' => Env::get('CACHE_EXPIRE', 86400, 'int')
];