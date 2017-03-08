<?php

/**
 * Synox console.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.2
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */

require('lib/common.php');
require('lib/SynoxInterface.php');
require('lib/SynoxAbstract.php');

@list (, $type, $first, $second) = $argv;
if (empty($type) || !in_array($type, ['bt', 'ht', 'au'])) {
    echo <<<'EOD'
for search: php synox.php bt "search query" "http://localhost:8080/"
for download: php synox.php ht "http://synox.loc/?id=PACKAGE&fetch=TORRENT_URL" "http://localhost:8080/"
for lyrics: php synox.php au "artist song" "title song" "http://localhost:8080/"
EOD;
    exit;
}

$name = ($type . '-synox');
$path = (__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
$info = json_decode(file_get_contents($path . $name . DIRECTORY_SEPARATOR . 'INFO'));

require($path . $name . DIRECTORY_SEPARATOR . $info->module);

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
    // for module dlm
    case ('bt'):
        $module = new $info->class();
        $module->prepare($curl, $first);

        echo 'url:' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
        $response = curl_exec($curl);
        curl_close($curl);

        echo 'count:' . $module->parse(new SynoxAbstract(), $response) . PHP_EOL;
        break;

    // for module host
    case ('ht'):
        $module   = new $info->class($first);
        $download = $module->GetDownloadInfo();

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $download[DOWNLOAD_URL]);
        $response = curl_exec($curl);

        echo 'url:' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
        echo 'download:' . (strpos($response, 'announce') !== false ? 'success' : 'failure') . PHP_EOL;
        curl_close($curl);
        break;

    // for module aum
    case ('au'):
        $interface = new SynoxAbstract();
        $module    = new $info->class();

        echo 'count:' . $module->getLyricsList($first, $second, $interface) . PHP_EOL;
        echo 'lyrics:' . ($module->getLyrics($interface->getLyricsId(), $interface) ? 'success' : 'failure') . PHP_EOL;
        break;
}
