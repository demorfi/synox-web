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

    protected $password;
    protected $maskQuery = '%s/api/lyrics?type=search&name=%s&api-key=%s';
    protected $maskFetch = '%s/api/lyrics?type=fetch&id=%s&url=%s&api-key=%s';

    public function __construct()
    {
        $config = @json_decode(file_get_contents('/tmp/synox.json'), true);
        if (!empty($config)) {
            $this->username = rtrim(trim($config['host']), '/');
            $this->password = trim($config['key']);
        }

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

    public function getLyricsList($artist, $title, $plugin)
    {
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
