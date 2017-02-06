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

    protected $password;

    protected $maskQuery = '%s/api/download?type=search&name=%s&api-key=%s';

    protected $maskVerify = '%s/api/?api-key=%s';

    protected $maskFetch = 'http://synox.loc/?id=%s&fetch=%s';

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

    public function prepare($curl, $query, $username = null, $password = null)
    {
        $this->username = rtrim(trim($username), '/');
        $this->password = trim($password);
        $this->query    = urlencode($query);

        $fullQuery = sprintf($this->maskQuery, $this->username, $this->query, $this->password);
        curl_setopt($curl, CURLOPT_URL, $fullQuery);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        return (true);
    }

    public function VerifyAccount($username, $password)
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->maskVerify, rtrim(trim($username), '/'), trim($password)));

        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        return (isset($response['success']) && $response['success'] == true);
    }

    public function parse($plugin, $response)
    {
        $response = @json_decode($response, true);
        if (!empty($response) && isset($response['chunks'])) {
            foreach ($response['chunks'] as $item) {
                $plugin->addResult(
                    $item['title'] . ' [' . $item['package'] . ']',
                    sprintf($this->maskFetch, $item['id'], urlencode($item['fetch'])),
                    $item['_size'],
                    $item['date'],
                    $item['page'],
                    md5($item['page'] . $item['title'] . $item['fetch']),
                    $item['seeds'],
                    $item['peers'],
                    $item['category']
                );
            }
            return (sizeof($response['chunks']));
        }

        return (0);
    }
}
