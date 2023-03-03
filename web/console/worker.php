<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

if (php_sapi_name() != 'cli') {
    die('Only works through cli!');
}

$options = getopt('', ['queue:', 'waiting::', 'hash:']);
if (empty($options) || isset($options['queue'], $options['waiting'])) {
    die('Queue is empty or invalid input parameters!');
}

if (isset($options['waiting']) && !isset($options['hash'])) {
    die('Hash required');
}

require_once ROOT_PATH . '/bootstrap.php';

use App\Package\PackageWorker;

if (isset($options['queue'])) {
    $queue = (array)unserialize((string)base64_decode($options['queue'], true));
    if (empty($queue) || sizeof($queue) < 3) {
        die('Queue is broken!');
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