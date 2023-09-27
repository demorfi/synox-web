<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

if (php_sapi_name() != 'cli') {
    exit("only works through cli!\n");
}

$options = getopt('', ['queue:', 'watchdog:', 'service::']);
if (empty($options) || sizeof($options) > 1) {
    exit("invalid input parameters!\n");
}

require_once ROOT_PATH . '/bootstrap.php';

use App\Package\Worker;
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
            exit($worker->notify('queue is broken!') . "\n");
        }

        $worker->queue(...([$hash, $query, $package] = $queue));

        // Queue completion tracking
        if (SharedMemory::has($hash)) {
            $storage = Storage::makeSharedMemory($hash);
            $threads = (int)$storage->read();
            $storage->rewrite((string)(--$threads));
        }
        exit;
    }

    // Run queue watchdog
    if (isset($options['watchdog'])) {
        $hash = $options['watchdog'];
        if (!SharedMemory::has($hash)) {
            exit($worker->notify('queue ends early!') . "\n");
        }

        $worker->watchdog($hash);
        exit;
    }
} catch (Exception $e) {
    exit($e->getMessage());
}

exit(1);