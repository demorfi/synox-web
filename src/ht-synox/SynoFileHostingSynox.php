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
    protected $curl;

    protected $username;

    protected $password;

    protected $maskFetch = '%s/api/download?type=fetch&id=%s&url=%s&api-key=%s';

    protected $maskVerify = '%s/api/?api-key=%s';

    protected $url;

    public function __construct($url, $username, $password)
    {
        $query = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        list($id, $fetch) = [$query['id'], urlencode($query['fetch'])];

        $this->username = rtrim(trim($username), '/');
        $this->password = trim($password);
        $this->url      = sprintf($this->maskFetch, $this->username, $id, $fetch, $this->password);

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

    public function Verify()
    {
        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->maskVerify, $this->username, $this->password));

        $response = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        return (isset($response['success']) && $response['success'] == true ? USER_IS_PREMIUM : LOGIN_FAIL);
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
            curl_setopt($curl, CURLOPT_URL, $this->url);

            $response = @json_decode(curl_exec($curl), true);
            curl_close($curl);

            if (isset($response['success']) && $response['success'] == true) {
                return ([DOWNLOAD_URL => $response['file']]);
            }

            return ([DOWNLOAD_ERROR => ERR_FILE_NO_EXIST]);
        }

        return ([DOWNLOAD_ERROR => LOGIN_FAIL]);
    }
}
