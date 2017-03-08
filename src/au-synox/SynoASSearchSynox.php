<?php

/**
 * Synology Audio Station Translate Song Text.
 * For translate song text to bananan.org.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoASSearchSynox
{
    protected $curl;
    protected $query = '';
    protected $username;

    protected $searchQuery = '%s/lyrics/search';
    protected $resultsQuery = '%s/lyrics/results';
    protected $maskFetch = '%s/lyrics/api/lyrics?type=fetch&id=%s&url=%s&api-key=%s';

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
     * Close curl resource.
     *
     * @access public
     */
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

        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->username, $this->query));
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

    public function getLyricsList($artist, $title, $plugin)
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, rtrim(trim($username), '/')));




        exit;
        $curl = curl_copy_handle($this->curl);
        $fullQuery = sprintf($this->maskQuery, $this->username, urlencode($artist . ' - ' . $title), $this->password);
        curl_setopt($curl, CURLOPT_URL, $fullQuery);

        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (!empty($response) && isset($response['chunks'])) {
            foreach ($response['chunks'] as $item) {
                $plugin->addTrackInfoToList(
                    $item['artist'],
                    $item['title'],
                    sprintf($this->maskFetch, $this->username, $item['id'], urlencode($item['fetch']), $this->password),
                    null
                );
            }
            return (sizeof($response['chunks']));
        }

        return (0);
    }

    public function getLyrics($id, $plugin)
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, $id);

        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (isset($response['success']) && $response['success'] == true) {
            $plugin->addLyrics($response['data']['content'], $id);
            return (true);
        }

        return (false);
    }
}
