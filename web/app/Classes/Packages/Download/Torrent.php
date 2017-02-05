<?php

namespace Classes\Packages\Download;

class Torrent implements \JsonSerializable
{
    /**
     * Url to file.
     *
     * @var string
     */
    private static $urlPrefix = '/torrents/';

    /**
     * Available torrent file.
     *
     * @var bool
     */
    private $available = false;

    /**
     * Path to file.
     *
     * @var string
     */
    private $path;

    /**
     * File name.
     *
     * @var string
     */
    private $name;

    /**
     * Torrent constructor.
     */
    public function __construct()
    {
        $this->path = PUBLIC_PATH . self::$urlPrefix;
    }

    /**
     * Is available file.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return ($this->available);
    }

    /**
     * Create new file.
     *
     * @param string $name
     * @param string $content
     * @return self
     */
    public function create($name, $content)
    {
        $this->name = ltrim($name, DIRECTORY_SEPARATOR);
        if (file_put_contents($this->path . $this->name, $content, LOCK_EX)) {
            $this->available = true;
        }

        return ($this);
    }

    /**
     * Open exists file.
     *
     * @param string $name
     * @return self
     */
    public function open($name)
    {
        $this->name = ltrim($name, DIRECTORY_SEPARATOR);
        $filePath   = $this->path . $this->name;
        if (is_readable($filePath) && filesize($filePath)) {
            $this->available = true;
        }

        return ($this);
    }

    /**
     * Fetch file content.
     *
     * @return null|string
     */
    public function fetch()
    {
        if ($this->isAvailable()) {
            return (file_get_contents($this->path . $this->name));
        }

        return (null);
    }

    /**
     * Get file name.
     *
     * @return string
     */
    public function getName()
    {
        return ($this->name);
    }

    /**
     * Get path to file.
     *
     * @return string
     */
    public function getPath()
    {
        return ($this->path);
    }

    /**
     * Get url to file.
     *
     * @return string
     */
    public function getFileUrl()
    {
        return (self::$urlPrefix . $this->name);
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        return (get_object_vars($this));
    }
}