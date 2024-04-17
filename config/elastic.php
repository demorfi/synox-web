<?php declare(strict_types=1);

use Digua\Env;

return [
    'hosts'    => Env::get('ELASTIC_HOST', 'localhost:9200', 'string'),
    'api_id'   => Env::get('ELASTIC_API_ID', null, 'string'),
    'api_key'  => Env::get('ELASTIC_API_KEY', null, 'string'),
    'cloud_id' => Env::get('ELASTIC_CLOUD_ID', null, 'string'),
    'timeout'  => Env::get('ELASTIC_TIMEOUT', 1.5, 'float')
];