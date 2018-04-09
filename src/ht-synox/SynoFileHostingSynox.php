<?php

/**
 * Synology Download Station Hosting File.
 * For download torrent files to ruracker.org.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoFileHostingSynox
{
    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $searchQuery = '%s/downloads/search';

    /**
     * @var string
     */
    protected $fetchQuery = '%s/downloads/fetch';

    /**
     * SynoFileHostingSynox constructor.
     *
     * @param string $url
     * @param string $username
     */
    public function __construct($url, $username)
    {
        $query = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        list($this->id, $this->url) = [$query['id'], urlencode($query['fetch'])];

        $this->username = rtrim(trim($username), '/');
        $this->curl     = curl_init();

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
     * @return int
     */
    public function Verify()
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->searchQuery, $this->username));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['name' => 'verify']));

        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        return (isset($response['success'], $response['hash']) ? USER_IS_PREMIUM : LOGIN_FAIL);
    }

    /**
     * Get information download torrent.
     *
     * @access public
     * @return array
     */
    public function GetDownloadInfo()
    {
        if ($this->Verify() === USER_IS_PREMIUM) {

            $curl = curl_copy_handle($this->curl);
            curl_setopt($curl, CURLOPT_URL, sprintf($this->fetchQuery, $this->username));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['id' => $this->id, 'url' => $this->url]));

            $response = @json_decode(curl_exec($curl), true);
            curl_close($curl);

            if (isset($response['success'], $response['file'])) {
                return ([DOWNLOAD_URL => ($this->username . $response['file']['url'])]);
            }

            return ([DOWNLOAD_ERROR => ERR_FILE_NO_EXIST]);
        }

        return ([DOWNLOAD_ERROR => LOGIN_FAIL]);
    }
}
