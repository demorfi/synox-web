<?php declare(strict_types=1);

use Digua\Env;

return [
    'hosts'    => Env::get('ELASTIC_HOST', 'localhost:9200'),
    'api_id'   => Env::get('ELASTIC_API_ID', null),
    'api_key'  => Env::get('ELASTIC_API_KEY', null),
    'cloud_id' => Env::get('ELASTIC_CLOUD_ID', null),
    'timeout'  => Env::get('ELASTIC_TIMEOUT', 1.5)
];