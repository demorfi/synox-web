<?php

/**
 * Synox console.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.2
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */

require('lib/common.php');
require('lib/SynoxInterface.php');
require('lib/SynoxAbstract.php');

$moduleType = (isset($argv[1]) ? $argv[1] : null);
$moduleName = (isset($argv[2]) ? $argv[2] : null);
// TODO: This
if (empty($moduleName) || empty($moduleType)) {
    echo 'for search: php syno.php bt "module name" "search query" ["username"] [:"password"]' . PHP_EOL
        . 'for download: php syno.php ht "module name" "url torrent file" ["username"] [:"password"]' . PHP_EOL
        . 'for lyrics: php syno.php au "module name" "artist song" "title song"' . PHP_EOL;
    exit;
}

$first  = isset($argv[3]) ? $argv[3] : '';
$second = isset($argv[4]) ? $argv[4] : null;
$third  = isset($argv[5]) ? $argv[5] : null;

$moduleName = $moduleType . '-' . $moduleName;
$modulePath = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
$infoObj    = json_decode(file_get_contents($modulePath . $moduleName . DIRECTORY_SEPARATOR . 'INFO'));

include($modulePath . $moduleName . DIRECTORY_SEPARATOR . $infoObj->module);

$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, DOWNLOAD_TIMEOUT);
curl_setopt($curl, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT);
curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);

// Work module dlm
if ($moduleType === 'bt') {

    /* @var $moduleObj SynoxInterface */
    $moduleObj = new $infoObj->class();
    $moduleObj->prepare($curl, $first, $second, $third);

    echo 'url:' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
    $response = curl_exec($curl);
    curl_close($curl);

    $count = $moduleObj->parse(new SynoxAbstract(), $response);
    echo 'count:' . $count . PHP_EOL;
}

// Work module host
if ($moduleType === 'ht') {

    /* @var $moduleObj SynoxInterface */
    $moduleObj = new $infoObj->class($first, $second, $third, array());
    $download  = $moduleObj->GetDownloadInfo();
    var_dump($download);

    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_URL, $download[DOWNLOAD_URL]);
    $response = curl_exec($curl);

    echo 'url:' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
    echo 'download:' . (strpos($response, 'announce') !== false ? 'success' : 'failure') . PHP_EOL;
    file_put_contents('/tmp/' . $moduleName . '.torrent', $response);
    curl_close($curl);
}

// Work module aum
if ($moduleType === 'au') {
    $interface = new SynoxAbstract();

    /* @var $moduleObj SynoxInterface */
    $moduleObj = new $infoObj->class();

    echo 'count:' . $moduleObj->getLyricsList($first, $second, $interface) . PHP_EOL;
    echo 'lyrics:' . ($moduleObj->getLyrics($interface->getLyricsId(), $interface)
            ? 'success' : 'failure') . PHP_EOL;
}

