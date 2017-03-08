<?php

/**
 * Synology Download Station Search File.
 * For search torrent files to ruracker.org.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchSynox
{
    protected $curl;

    protected $query = '';

    protected $username;

    protected $searchQuery = '%s/downloads/search';

    protected $resultsQuery = '%s/downloads/results';

    protected $fetchQuery = 'http://synox.loc/?id=%s&fetch=%s';


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

    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function prepare($curl, $query, $username = null)
    {
        $this->username = rtrim(trim($username), '/');
        $this->query    = urlencode($query);

        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->username));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => $query]));

        return (true);
    }

    public function VerifyAccount($username)
    {
        $curl = curl_copy_handle($this->curl);

        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, rtrim(trim($username), '/')));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => 'verify']));

        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        return (isset($response['success'], $response['hash']));
    }

    public function parse($plugin, $response)
    {
        $total    = 0;
        $response = @json_decode($response, true);
        if (!empty($response) && isset($response['hash'])) {
            $mh = curl_multi_init();
            $ch = [];

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

            $ch['results'] = curl_copy_handle($this->curl);
            curl_setopt($ch['results'], CURLOPT_URL, sprintf($this->resultsQuery, $this->username));
            curl_setopt($ch['results'], CURLOPT_POST, true);
            curl_setopt($ch['results'], CURLOPT_POSTFIELDS, http_build_query(['hash' => $response['hash']]));

            curl_multi_add_handle($mh, $ch['search']);
            curl_multi_add_handle($mh, $ch['results']);

            while (curl_multi_exec($mh, $running) == CURLM_CALL_MULTI_PERFORM) {
                ;
            }

            usleep(100000);
            $status = curl_multi_exec($mh, $running);
            while ($running > 0 && $status == CURLM_OK) {
                curl_multi_select($mh, 4);
                usleep(500000);

                while (($status = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM) {
                    ;
                }

                while (($info = curl_multi_info_read($mh)) != false) {
                    $handle   = $info['handle'];
                    $one      = curl_getinfo($handle);
                    $response = curl_multi_getcontent($handle);

                    curl_multi_remove_handle($mh, $handle);
                    curl_close($handle);

                    if ($one['http_code'] == 200 && $one['url'] == sprintf($this->resultsQuery, $this->username)) {
                        $response = @json_decode($response, true);
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
