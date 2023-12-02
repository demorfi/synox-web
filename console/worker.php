<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

if (php_sapi_name() != 'cli') {
    exit("Only works through cli!\n");
}

$options = getopt('', ['queue:', 'watchdog:', 'service::']);
if (empty($options) || sizeof($options) > 1) {
    exit("Invalid input parameters!\n");
}

require_once ROOT_PATH . '/bootstrap.php';

use app\Package\Search\Worker;
use Digua\Components\{Storage, Storage\SharedMemory};

try {
    $worker = new Worker;

    // Run service WorkerConnection
    if (isset($options['service'])) {
        $worker->service();
        exit;
    }

    // Run one queue for search in the package
    if (isset($options['queue'])) {
        $queue = (array)unserialize((string)base64_decode($options['queue'], true));
        if (empty($queue) || sizeof($queue) < 3) {
            exit($worker->notify('Queue is broken!') . "\n");
        }

        $worker->queue(...([$token, $query, $package] = $queue));

        // Queue completion tracking
        if (SharedMemory::has($token)) {
            $storage = Storage::makeSharedMemory($token);
            $threads = (int)$storage->read();
            $storage->rewrite((string)(--$threads));
        }
        exit;
    }

    // Run queue watchdog
    if (isset($options['watchdog'])) {
        $token = $options['watchdog'];
        if (!SharedMemory::has($token)) {
            exit($worker->notify('Queue ends early!') . "\n");
        }

        $worker->watchdog($token);
        exit;
    }
} catch (Exception $e) {
    exit($e->getMessage());
}

exit(1);