<?php declare(strict_types=1);

const DOCUMENT_ROOT = __DIR__;
define('ROOT_PATH', realpath(DOCUMENT_ROOT . '/..'));

require_once ROOT_PATH . '/bootstrap.php';

use Digua\{Request, Routes\RouteAsNameBuilder};

$usesApi      = str_starts_with($_SERVER['REQUEST_URI'], '/api/');
$request      = new Request;
$appEntryPath = null;

if ($usesApi) {
    $request->getData()->query()->exportFromPath(1);
    $appEntryPath = '\App\Controllers\Api\\';
}

$builder = new RouteAsNameBuilder($request);
if (!$usesApi) {
    $builder->forced(App\Controllers\Main::class, 'default');
}

print (new Digua\RouteDispatcher())->default($builder, $appEntryPath, App\Controllers\Error::class);