<?php

/**
 * SynoX (use at Synology Download Station HT)
 * Download torrents files across synox console.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoFileHostingSynox
{
    /**
     * Curl instance.
     *
     * @var resource
     */
    protected $curl;

    /**
     * Username used at url to synox console.
     * Example: https://synox.synology.loc/ (Web Station).
     *
     * @var string
     */
    protected $username;

    /**
     * Password used at enable/disabled debug mode.
     * For enable debug mode set the password value to "test".
     *
     * @var string
     */
    protected $password;

    /**
     * Use debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Id package.
     *
     * @var string
     */
    protected $id;

    /**
     * Fetch torrent url.
     *
     * @var string
     */
    protected $url;

    /**
     * Search torrents query (Use for only verify host).
     *
     * @var string
     */
    protected $searchQuery = '%s/downloads/search';

    /**
     * Fetch query for torrent file.
     *
     * @var string
     */
    protected $fetchQuery = '%s/downloads/fetch';

    /**
     * Path to log file.
     *
     * @var string
     */
    protected $logFile = '/tmp/ht-synox.log';

    /**
     * SynoFileHostingSynox constructor.
     *
     * @param string $url      Url to download torrent
     * @param string $username Username for auth
     * @param string $password Password for auth
     */
    public function __construct($url, $username, $password = null)
    {
        $query = [];

        // Set debug mode
        $this->debug = ($password == 'test');

        // Username used at url to synox console. (Example: https://synox.synology.loc/ (Web Station))
        $this->username = rtrim(trim($username), '/');

        // Parse query to id package and fetch url
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        list($this->id, $this->url) = [$query['id'], urlencode($query['fetch'])];
        $this->debug('prepare: ' . json_encode([$this->username, $query, $this->id, $this->url]));

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($this->curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * SynoFileHostingSynox destructor.
     */
    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * Send debug message to log file.
     *
     * @param string $message
     * @return void
     */
    protected function debug($message)
    {
        if ($this->debug) {
            file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
            if (function_exists('syslog')) {
                syslog(LOG_INFO, $message);
            }
        }
    }

    /**
     * Check auth account to tracker.
     *
     * @return int
     */
    public function Verify()
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->username));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => 'verify']));

        $this->debug('verify: ' . json_encode([$this->username]));
        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        $this->debug('verify: ' . json_encode([$response]));
        return (isset($response['success'], $response['hash']) ? USER_IS_PREMIUM : LOGIN_FAIL);
    }

    /**
     * Get information download torrent.
     *
     * @return array
     */
    public function GetDownloadInfo()
    {
        if ($this->Verify() === USER_IS_PREMIUM) {

            $curl = curl_copy_handle($this->curl);
            curl_setopt($curl, CURLOPT_URL, sprintf($this->fetchQuery, $this->username));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['id' => $this->id, 'url' => $this->url]));

            $this->debug('info: ' . json_encode([$this->username, $this->id, $this->url]));
            $response = @json_decode(curl_exec($curl), true);
            curl_close($curl);

            $this->debug('info: ' . json_encode([$response]));
            if (isset($response['success'], $response['file'])) {
                return ([DOWNLOAD_URL => ($this->username . $response['file']['url'])]);
            }

            return ([DOWNLOAD_ERROR => ERR_FILE_NO_EXIST]);
        }

        return ([DOWNLOAD_ERROR => LOGIN_FAIL]);
    }
}
