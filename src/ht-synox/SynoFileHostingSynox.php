<?php declare(strict_types=1);

/**
 * SynoX (use at Synology Download Station HT)
 * Download torrents files across synox web console.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 2.0
 * @php-dsm 7.2
 * @source https://github.com/demorfi/synox
 * @license https://opensource.org/license/mit/
 */
class SynoFileHostingSynox
{
    /**
     * @var resource
     */
    private $curl;

    /**
     * Username used at url to synox console.
     * Example: https://synox.synology.loc/ (Web Station).
     * Password used at enable/disabled debug mode.
     * For enable debug mode set the password value to "test".
     *
     * @var string
     */
    private $host;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $searchQuery = '%s/download/search';

    /**
     * @var string
     */
    private $fetchQuery = '%s/download/fetch';

    /**
     * @var string
     */
    private $logFile = '/tmp/ht-synox.log';

    /**
     * @param string  $url
     * @param string  $username
     * @param ?string $password
     */
    public function __construct(string $url, string $username, string $password = null)
    {
        // Set debug mode
        $this->debug = ($password == 'test');

        // Username used at url to synox console. (Example: https://synox.synology.loc/ (Web Station))
        $this->host = rtrim(trim($username), '/');

        // Parse query to id package and fetch url
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        [$this->id, $this->url] = [$query['id'], urlencode($query['fetch'])];
        $this->debug('prepare: ' . json_encode([$this->host, $query, $this->id, $this->url]));

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, DOWNLOAD_TIMEOUT);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT);
        curl_setopt($this->curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * @param string $message
     * @return void
     */
    private function debug(string $message)
    {
        if ($this->debug) {
            if (php_sapi_name() == 'cli') {
                print $message . PHP_EOL;
            } else {
                file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
                if (function_exists('syslog')) {
                    syslog(LOG_INFO, $message);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function Verify(): int
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->host));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['query' => 'verify']));

        $this->debug('verify: ' . json_encode([$this->host]));
        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        $this->debug('verify: ' . json_encode([$response]));
        return isset($response['success'], $response['hash']) ? USER_IS_PREMIUM : LOGIN_FAIL;
    }

    /**
     * @return array
     */
    public function GetDownloadInfo(): array
    {
        if ($this->Verify() === USER_IS_PREMIUM) {
            $curl = curl_copy_handle($this->curl);
            curl_setopt($curl, CURLOPT_URL, sprintf($this->fetchQuery, $this->host));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['id' => $this->id, 'url' => $this->url]));

            $this->debug('info: ' . json_encode([$this->host, $this->id, $this->url]));
            $response = @json_decode(curl_exec($curl), true);
            curl_close($curl);

            $this->debug('info: ' . json_encode([$response]));
            if (isset($response['success'], $response['file'])) {
                return [DOWNLOAD_URL => $this->host . $response['file']['url']];
            }

            return [DOWNLOAD_ERROR => ERR_FILE_NO_EXIST];
        }

        return [DOWNLOAD_ERROR => LOGIN_FAIL];
    }
}
