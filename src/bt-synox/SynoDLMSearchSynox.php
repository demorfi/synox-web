<?php declare(strict_types=1);

/**
 * SynoX (use at Synology Download Station BT)
 * Search torrents files across synox web console.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 2.0
 * @php-dsm 7.2
 * @source https://github.com/demorfi/synox
 * @license https://opensource.org/license/mit/
 */
class SynoDLMSearchSynox
{
    /**
     * @var resource
     */
    private $curl;

    /**
     * @var string
     */
    private $query = '';

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
    private $searchQuery = '%s/download/search';

    /**
     * @var string
     */
    private $resultsQuery = '%s/download/results';

    /**
     * @var string
     */
    private $fetchQuery = '%s/download/fetch/?id=%s&fetch=%s';

    /**
     * @var string
     */
    private $logFile = '/tmp/bt-synox.log';

    public function __construct()
    {
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
     * Send debug message to log file.
     *
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
     * @param resource $curl
     * @param string   $query
     * @param string   $username
     * @param ?string  $password
     * @return bool
     */
    public function prepare($curl, string $query, string $username, string $password = null): bool
    {
        $this->query = urlencode($query);

        // Set debug mode
        $this->debug = ($password == 'test');

        // Username used at url to synox console. (Example: https://synox.synology.loc/ (Web Station))
        $this->host = rtrim(trim($username), '/');

        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->host));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['query' => $this->query]));

        $this->debug('prepare: ' . json_encode([$this->host, $this->query]));
        return true;
    }

    /**
     * @param string  $username
     * @param ?string $password
     * @return bool
     */
    public function VerifyAccount(string $username, string $password = null): bool
    {
        // Set debug mode
        $this->debug = ($password == 'test');

        // Username used at url to synox console. (Example: https://synox.synology.loc/ (Web Station))
        $this->host = rtrim(trim($username), '/');

        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->host));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['query' => 'verify']));

        $this->debug('verify: ' . json_encode([$this->host]));
        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        $this->debug('verify: ' . json_encode([$response]));
        return isset($response['success'], $response['hash']);
    }

    /**
     * @param SynoxAbstract $plugin
     * @param string        $response
     * @return int
     */
    public function parse($plugin, string $response): int
    {
        $total    = 0;
        $response = @json_decode($response, true);

        $this->debug('parse: ' . json_encode([$response]));
        if (!empty($response) && isset($response['hash'])) {
            $mh = curl_multi_init();
            $ch = [];

            $this->debug('search: ' . json_encode([$this->host, $this->query, $response]));
            $ch['search'] = curl_copy_handle($this->curl);
            curl_setopt($ch['search'], CURLOPT_URL, sprintf($this->searchQuery, $this->host));
            curl_setopt($ch['search'], CURLOPT_POST, true);
            curl_setopt(
                $ch['search'],
                CURLOPT_POSTFIELDS,
                http_build_query(
                    [
                        'query'   => $this->query,
                        'filters' => ['category' => null],
                        'hash'    => $response['hash']
                    ]
                )
            );

            $this->debug('results: ' . json_encode([$this->host, $response]));
            $ch['results'] = curl_copy_handle($this->curl);
            curl_setopt($ch['results'], CURLOPT_URL, sprintf($this->resultsQuery, $this->host));
            curl_setopt($ch['results'], CURLOPT_POST, true);
            curl_setopt($ch['results'], CURLOPT_POSTFIELDS, http_build_query(['hash' => $response['hash']]));

            curl_multi_add_handle($mh, curl_copy_handle($ch['search']));
            curl_multi_add_handle($mh, curl_copy_handle($ch['results']));

            do {
                $status = curl_multi_exec($mh, $running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);

            usleep(100000);
            $this->debug('search run: ' . json_encode([$running, $status]));

            while ($running > 0 && $status == CURLM_OK) {
                if (curl_multi_select($mh, 4) != -1) {
                    usleep(100000);
                    $this->debug('after sleep: ' . json_encode([$running, $status]));

                    do {
                        $status = curl_multi_exec($mh, $running);
                    } while ($status == CURLM_CALL_MULTI_PERFORM);

                    while (($info = curl_multi_info_read($mh)) != false) {
                        $handle   = $info['handle'];
                        $one      = curl_getinfo($handle);
                        $response = curl_multi_getcontent($handle);

                        curl_multi_remove_handle($mh, $handle);
                        curl_close($handle);
                        $this->debug('http: ' . json_encode([$one]));
                        $this->debug('response: ' . json_encode([$response]));

                        // Only results response
                        if ($one['http_code'] == 200 && $one['url'] == sprintf($this->resultsQuery, $this->host)) {
                            $response = @json_decode($response, true);
                            if (isset($response['chunks']) && !empty($response['chunks'])) {
                                foreach ($response['chunks'] as $item) {
                                    $hash     = md5($item['pageUrl'] . $item['title'] . $item['fetchUrl']);
                                    $title    = urldecode($item['title']) . ' [' . $item['package'] . ']';
                                    $download = sprintf(
                                        $this->fetchQuery,
                                        $this->host,
                                        $item['id'],
                                        urlencode($item['fetchUrl'])
                                    );

                                    $plugin->addResult(
                                        $title,
                                        $download,
                                        (float)$item['size'],
                                        $item['date'],
                                        $item['pageUrl'],
                                        $hash,
                                        (int)$item['seeds'],
                                        (int)$item['peers'],
                                        urldecode($item['category'])
                                    );

                                    $total++;
                                }
                            }

                            if (!isset($response['isEnd']) || !$response['isEnd']) {
                                curl_multi_add_handle($mh, curl_copy_handle($ch['results']));
                                curl_multi_exec($mh, $running);
                            }
                        }
                    }
                }
            }

            curl_multi_close($mh);
        }

        return $total;
    }
}
