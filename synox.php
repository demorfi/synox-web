<?php declare(strict_types=1);

if (php_sapi_name() != 'cli') {
    exit("only works through cli!\n");
}

define('SRC_PATH', realpath(__DIR__ . '/src'));

$options = getopt('', ['command:', 'query:', 'host::', 'debug::', 'help::']);
if (empty($options) || isset($options['help']) || sizeof($options) < 2) {
    echo <<<'EOD'
for search torrents: 
    --command download --query "search query string" [--host "http://synox host/"] [--debug]
for search lyrics: 
    --command lyrics --query "artist song/title song" [--host "http://synox host/"] [--debug]
for download: 
    --command fetch --query "search result link" [--host "http://synox host/"] [--debug]

EOD;
    exit;
}

$host = $options['host'] ?? $_ENV['SYNOX_HOST'] ?? false;
if (empty($host)) {
    exit("synox host not found!\n");
}

$type = ['download' => 'bt', 'fetch' => 'ht', 'lyrics' => 'au'][$options['command']] ?? false;
if (empty($type)) {
    exit("unknown command!\n");
}

$pathInfo = SRC_PATH . '/' . $type . '-synox/INFO';
if (!is_file($pathInfo)) {
    exit("info file is not found!\n");
}

require_once('lib/common.php');
require_once('lib/SynoxInterface.php');
require_once('lib/SynoxAbstract.php');

$info  = json_decode(file_get_contents($pathInfo));
$debug = isset($options['debug']) ? 'test' : null;

$pathModule = SRC_PATH . '/' . $type . '-synox/' . $info->module;
if (!is_file($pathModule)) {
    exit("module file is not found!\n");
}

require_once($pathModule);

$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, DOWNLOAD_TIMEOUT);
curl_setopt($curl, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT);
curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);

/* @var $module SynoxInterface */
switch ($type) {
    // Search torrent files
    case ('bt'):
        $module = new $info->class();
        $module->prepare($curl, $options['query'], $host, $debug);

        echo 'url:' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
        $response = curl_exec($curl);
        curl_close($curl);

        echo 'count:' . $module->parse(new SynoxAbstract(), $response) . PHP_EOL;
        break;
    // Download torrent files
    case ('ht'):
        $module   = new $info->class(stripslashes($options['query']), $host, $debug);
        $download = $module->GetDownloadInfo();

        if (isset($download[DOWNLOAD_ERROR])) {
            echo 'error: ' . $download[DOWNLOAD_ERROR] . PHP_EOL;
            break;
        }

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $download[DOWNLOAD_URL]);
        $response = curl_exec($curl);

        echo 'url:' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
        echo 'download:' . (strpos($response, 'announce') !== false ? 'success' : 'failure') . PHP_EOL;
        curl_close($curl);
        break;
    // Search song text
    case ('au'):
        $interface = new SynoxAbstract();
        $module    = new $info->class();

        [$artist, $title] = array_pad(explode('/', $options['query']), 2, '');
        echo 'count:' . $module->getLyricsList($artist, $title, $interface) . PHP_EOL;
        echo 'lyrics:' . ($module->getLyrics($interface->getLyricsId(), $interface) ? 'success' : 'failure') . PHP_EOL;
        break;
}
