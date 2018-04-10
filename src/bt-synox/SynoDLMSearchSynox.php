<?php

/**
 * SynoX (use at Synology Download Station BT)
 * Search torrents files across synox console.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchSynox
{
    /**
     * Curl instance.
     *
     * @var resource
     */
    protected $curl;

    /**
     * Query request.
     *
     * @var string
     */
    protected $query = '';

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
     * Search torrents query.
     *
     * @var string
     */
    protected $searchQuery = '%s/downloads/search';

    /**
     * Results torrents query.
     *
     * @var string
     */
    protected $resultsQuery = '%s/downloads/results';

    /**
     * Fetch query for torrent file.
     *
     * @var string
     */
    protected $fetchQuery = 'http://synox.loc/?id=%s&fetch=%s';

    /**
     * Path to log file.
     *
     * @var string
     */
    protected $logFile = '/tmp/bt-synox.log';

    /**
     * SynoDLMSearchSynox constructor.
     */
    public function __construct()
    {
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
     * SynoDLMSearchSynox destructor.
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
     * Send query to tracker.
     *
     * @param resource $curl     Resource curl
     * @param string   $query    Search query
     * @param string   $username Username for auth
     * @param string   $password Password for auth
     * @return bool
     */
    public function prepare($curl, $query, $username = null, $password = null)
    {
        $this->query = urlencode($query);

        // Set debug mode
        $this->debug = ($password == 'test');

        // Username used at url to synox console. (Example: https://synox.synology.loc/ (Web Station))
        $this->username = rtrim(trim($username), '/');

        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->username));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => $this->query]));

        $this->debug('prepare: ' . json_encode([$this->username, $this->query]));
        return (true);
    }

    /**
     * Check auth account to tracker.
     *
     * @param string $username Username for auth
     * @param string $password Password for auth
     * @return bool
     */
    public function VerifyAccount($username, $password = null)
    {
        // Set debug mode
        $this->debug = ($password == 'test');

        // Username used at url to synox console. (Example: https://synox.synology.loc/ (Web Station))
        $this->username = rtrim(trim($username), '/');

        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->username));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => 'verify']));

        $this->debug('verify: ' . json_encode([$this->username]));
        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        $this->debug('verify: ' . json_encode([$response]));
        return (isset($response['success'], $response['hash']));
    }

    /**
     * Add torrent file in list.
     *
     * @param SynoxAbstract $plugin   Synology abstract
     * @param string        $response Content tracker page
     * @return int
     */
    public function parse($plugin, $response)
    {
        $total    = 0;
        $response = @json_decode($response, true);

        $this->debug('parse: ' . json_encode([$response]));
        if (!empty($response) && isset($response['hash'])) {
            $mh = curl_multi_init();
            $ch = [];

            $this->debug('search: ' . json_encode([$this->username, $this->query, $response]));
            $ch['search'] = curl_copy_handle($this->curl);
            curl_setopt($ch['search'], CURLOPT_URL, sprintf($this->searchQuery, $this->username));
            curl_setopt($ch['search'], CURLOPT_POST, true);
            curl_setopt(
                $ch['search'],
                CURLOPT_POSTFIELDS,
                http_build_query(
                    [
                        'name' => $this->query,
                        'hash' => $response['hash']
                    ]
                )
            );

            $this->debug('results: ' . json_encode([$this->username, $response]));
            $ch['results'] = curl_copy_handle($this->curl);
            curl_setopt($ch['results'], CURLOPT_URL, sprintf($this->resultsQuery, $this->username));
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

                        // Only results response
                        if ($one['http_code'] == 200 && $one['url'] == sprintf($this->resultsQuery, $this->username)) {
                            $response = @json_decode($response, true);
                            $this->debug('response: ' . json_encode([$response]));

                            if (isset($response['chunks'])) {
                                if (!empty($response['chunks'])) {
                                    foreach ($response['chunks'] as $item) {
                                        $plugin->addResult(
                                            $item['title'] . ' [' . $item['package'] . ']',
                                            sprintf($this->fetchQuery, $item['id'], urlencode($item['fetch'])),
                                            $item['_size'],
                                            $item['date'],
                                            $item['page'],
                                            md5($item['page'] . $item['title'] . $item['fetch']),
                                            $item['seeds'],
                                            $item['peers'],
                                            $item['category']
                                        );

                                        $total++;
                                    }
                                }
                            }

                            if (!isset($response['isEnd']) || $response['isEnd'] != true) {
                                curl_multi_add_handle($mh, curl_copy_handle($ch['results']));
                                curl_multi_exec($mh, $running);
                            }
                        }
                    }
                }
            }

            curl_multi_close($mh);
        }

        return ($total);
    }
}
