<?php declare(strict_types=1);

/**
 * Synology Audio Station Search Song Text.
 * Search song text across synox web console.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 2.0
 * @php-dsm 7.2
 * @source https://github.com/demorfi/synox
 * @license https://opensource.org/license/mit/
 */
class SynoASSearchSynox
{
    /**
     * @var resource
     */
    private $curl;

    /**
     * Url to synox web console.
     * Example: https://synox.synology.loc/ (Web Station).
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
    private $searchQuery = '%s/lyrics/search';

    /**
     * @var string
     */
    private $resultsQuery = '%s/lyrics/results';

    /**
     * @var string
     */
    private $fetchQuery = '%s/lyrics/fetch?&id=%s&fetch=%s';

    /**
     * @var string
     */
    private $logFile = '/tmp/au-synox.log';

    public function __construct()
    {
        // Settings of INFO file
        $info       = json_decode(file_get_contents(__DIR__ . '/INFO'));
        $this->host = rtrim(trim($info->host), '/');

        // Let's try to use the host address from the bt-synox module
        if ($this->host == 'http://0.0.0.0:8282' && is_file(SEARCH_ACCOUNT_CONF)) {
            $ini = parse_ini_string(file_get_contents(SEARCH_ACCOUNT_CONF), true);
            if (isset($ini['ht-synox']['username'])) {
                $this->host = $ini['ht-synox']['username'];
            }
        }

        $this->debug = (bool)$info->debug;
        $this->debug('load: ' . json_encode([$info]));
        $this->debug('uses host: ' . json_encode([$this->host]));

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
     * @return string
     */
    public function prepare(): string
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->host));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['query' => 'verify']));

        $this->debug('prepare: ' . json_encode([$this->host]));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * @param string        $artist Artist song
     * @param ?string       $title  Title song
     * @param SynoxAbstract $plugin Synology abstract
     * @return int
     */
    public function getLyricsList(string $artist, string $title, $plugin): int
    {
        $total    = 0;
        $response = @json_decode($this->prepare(), true);
        $query    = $artist . ' - ' . $title;

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
                        'query' => $query,
                        'hash'  => $response['hash']
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
                                    $plugin->addTrackInfoToList(
                                        urldecode($item['artist']),
                                        urldecode($item['title']),
                                        sprintf(
                                            $this->fetchQuery,
                                            $this->host,
                                            $item['id'],
                                            urlencode($item['fetchUrl'])
                                        ),
                                        $item['content'] ?? null
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

    /**
     * @param string        $id
     * @param SynoxAbstract $plugin
     * @return bool
     */
    public function getLyrics(string $id, $plugin): bool
    {
        $response = @json_decode($this->prepare(), true);
        $this->debug('parse-s: ' . json_encode([$response, $id]));

        // Parse query to id package and fetch url
        parse_str(parse_url($id, PHP_URL_QUERY), $query);
        [$_id, $url] = [$query['id'], urlencode($query['fetch'])];
        $this->debug('before: ' . json_encode([$this->host, $query, $_id, $url]));

        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->fetchQuery, $this->host, '', ''));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['id' => $_id, 'url' => $url]));
        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        $this->debug('info: ' . json_encode([$response]));
        if (isset($response['success'], $response['data'])) {
            $content = preg_replace('#<br\s*/?>#i', "\n", $response['data']['content']);
            $content = preg_replace('#\n\n*#', "\n", $content);
            $plugin->addLyrics($content, $id);
            return true;
        }

        return false;
    }
}
