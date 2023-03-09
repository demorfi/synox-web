<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

if (php_sapi_name() != 'cli') {
    exit("only works through cli!\n");
}

$options = getopt('', ['queue:', 'waiting::', 'hash:']);
if (empty($options) || isset($options['queue'], $options['waiting'])) {
    exit("queue is empty or invalid input parameters!\n");
}

if (isset($options['waiting']) && !isset($options['hash'])) {
    exit("hash required!\n");
}

require_once ROOT_PATH . '/bootstrap.php';

use App\Package\PackageWorker;

if (isset($options['queue'])) {
    $queue = (array)unserialize((string)base64_decode($options['queue'], true));
    if (empty($queue) || sizeof($queue) < 3) {
        exit("Queue is broken!\n");
    }

    [$hash, $package, $query] = $queue;
    (new PackageWorker($hash))->exec($package, $query);
    exit($hash);
}

if (isset($options['waiting'])) {
    (new PackageWorker($options['hash']))->wait();
    exit;
}

exit(1);