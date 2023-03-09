<?php declare(strict_types=1);

interface SynoxInterface
{
    /**
     * @param resource $curl
     * @param string   $query
     * @param string   $username
     * @param ?string  $password
     * @return bool
     */
    public function prepare($curl, string $query, string $username, ?string $password = null): bool;

    /**
     * @param SynoxAbstract $plugin
     * @param string        $response
     * @return int
     */
    public function parse(SynoxAbstract $plugin, string $response): int;

    /**
     * @return array
     */
    public function GetDownloadInfo(): array;

    /**
     * @param string        $artist
     * @param string        $title
     * @param SynoxAbstract $plugin
     * @return int
     */
    public function getLyricsList(string $artist, string $title, SynoxAbstract $plugin): int;

    /**
     * @param string        $id
     * @param SynoxAbstract $plugin
     * @return bool
     */
    public function getLyrics(string $id, SynoxAbstract $plugin): bool;
}
