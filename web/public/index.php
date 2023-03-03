<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once ROOT_PATH . '/bootstrap.php';
print (new Digua\RouteDispatcher())->default();
