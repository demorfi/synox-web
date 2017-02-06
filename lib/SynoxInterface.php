<?php

/**
 * Synox Interface.
 *
 * @author  demorfi <demorfi@gmail.com>
 * @version 1.1
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
interface SynoxInterface
{
    /**
     * Send query to tracker.
     *
     * @param resource $curl     Resource curl
     * @param string   $query    Search query
     * @param string   $username Username for auth
     * @param string   $password Password for auth
     * @access public
     * @return bool
     */
    public function prepare($curl, $query, $username = null, $password = null);

    /**
     * Add torrent file in list.
     *
     * @param SynoxAbstract $plugin   Synology abstract
     * @param string        $response Content tracker page
     * @access public
     * @return int
     */
    public function parse($plugin, $response);

    /**
     * Get information download torrent.
     *
     * @access public
     * @return array
     */
    public function GetDownloadInfo();

    /**
     * Search lyrics.
     *
     * @param string        $artist Artist song
     * @param string        $title  Title song
     * @param SynoxAbstract $plugin Synology abstract
     * @access public
     * @return int
     */
    public function getLyricsList($artist, $title, $plugin);

    /**
     * Add lyrics.
     *
     * @param string        $id     Id found lyric
     * @param SynoxAbstract $plugin Synology abstract
     * @access public
     * @return bool
     */
    public function getLyrics($id, $plugin);
}
