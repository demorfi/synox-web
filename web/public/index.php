<?php

define('PHP_EXT', '.php');
define('ROOT_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
define('SRC_PATH', ROOT_PATH . '/src');
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

spl_autoload_register(
    function ($className) {
        $path = '/' . str_replace('\\', '/', $className) . PHP_EXT;
        $dir  = (stripos($className, 'framework') !== false)
            ? SRC_PATH
            : APP_PATH;

        if (!is_file($dir . $path)) {
            throw new \Exception($path . ' - file not found');
        }

        require($dir . $path);
    }
);

include(APP_PATH . '/helpers.php');
new Framework\Route();