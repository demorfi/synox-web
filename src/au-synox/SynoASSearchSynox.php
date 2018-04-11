<?php

/**
 * Synology Audio Station Translate Song Text.
 * For translate song text to bananan.org.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoASSearchSynox
{
    /**
     * Curl instance.
     *
     * @var resource
     */
    protected $curl;

    /**
     * Url to synox console.
     * Example: https://synox.synology.loc/ (Web Station).
     *
     * @var string
     */
    protected $host;

    /**
     * Use debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Search lyrics query.
     *
     * @var string
     */
    protected $searchQuery = '%s/lyrics/search';

    /**
     * Results lyrics query.
     *
     * @var string
     */
    protected $resultsQuery = '%s/lyrics/results';

    /**
     * Fetch query for lyrics info.
     *
     * @var string
     */
    protected $fetchQuery = '%s/lyrics/fetch';

    /**
     * Mask fetch query for lyrics info.
     *
     * @var string
     */
    protected $maskFetch = '%s/lyrics/fetch?&id=%s&fetch=%s';

    /**
     * Path to log file.
     *
     * @var string
     */
    protected $logFile = '/tmp/au-synox.log';

    /**
     * SynoASSearchSynox constructor.
     */
    public function __construct()
    {
        // Settings of INFO file
        $info        = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'INFO'));
        $this->host  = rtrim(trim($info->host), '/');
        $this->debug = (bool)$info->debug;
        $this->debug('load: ' . json_encode([$info]));

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
     * SynoASSearchSynox destructor.
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
            exec('echo "' . addslashes($message) . '" >> ' . $this->logFile);
            if (function_exists('syslog')) {
                syslog(LOG_INFO, $message);
            }
        }
    }

    /**
     * Send query to lyric.
     *
     * @return string
     */
    public function prepare()
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->host));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => 'verify']));

        $this->debug('prepare: ' . json_encode([$this->host]));
        $response = curl_exec($curl);
        curl_close($curl);

        return ($response);
    }

    /**
     * Search lyrics.
     *
     * @param string        $artist Artist song
     * @param string        $title  Title song
     * @param SynoxAbstract $plugin Synology abstract
     * @return int
     */
    public function getLyricsList($artist, $title, $plugin)
    {
        $total    = 0;
        $response = @json_decode($this->prepare(), true);
        $query    = urlencode($artist . ' - ' . $title);

        $this->debug('parse-l: ' . json_encode([$response]));
        if (!empty($response) && isset($response['hash'])) {
            $mh = curl_multi_init();
            $ch = [];

            $this->debug('search: ' . json_encode([$this->host, $query, $response]));
            $ch['search'] = curl_copy_handle($this->curl);
            curl_setopt($ch['search'], CURLOPT_URL, sprintf($this->searchQuery, $this->host));
            curl_setopt($ch['search'], CURLOPT_POST, true);
            curl_setopt(
                $ch['search'],
                CURLOPT_POSTFIELDS,
                http_build_query(
                    [
                        'name' => $query,
                        'hash' => $response['hash']
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

                        // Only results response
                        if ($one['http_code'] == 200 && $one['url'] == sprintf($this->resultsQuery, $this->host)) {
                            $response = @json_decode($response, true);
                            $this->debug('response: ' . json_encode([$response]));

                            if (isset($response['chunks'])) {
                                if (!empty($response['chunks'])) {
                                    foreach ($response['chunks'] as $item) {
                                        $plugin->addTrackInfoToList(
                                            $item['artist'],
                                            $item['title'],
                                            sprintf(
                                                $this->maskFetch,
                                                $this->host,
                                                $item['id'],
                                                urlencode($item['fetch'])
                                            ),
                                            null
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

    /**
     * Get lyrics.
     *
     * @param string        $id     Id found lyric
     * @param SynoxAbstract $plugin Synology abstract
     * @return bool
     */
    public function getLyrics($id, $plugin)
    {
        $response = @json_decode($this->prepare(), true);
        $this->debug('parse-s: ' . json_encode([$response, $id]));

        // Parse query to id package and fetch url
        parse_str(parse_url($id, PHP_URL_QUERY), $query);
        list($_id, $url) = [$query['id'], urlencode($query['fetch'])];
        $this->debug('before: ' . json_encode([$this->host, $query, $_id, $url]));

        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->fetchQuery, $this->host));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['id' => $_id, 'url' => $url]));
        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        $this->debug('info: ' . json_encode([$response]));
        if (isset($response['success'], $response['data'])) {
            $plugin->addLyrics($response['data']['content'], $id);
            return (true);
        }

        return (false);
    }
}
