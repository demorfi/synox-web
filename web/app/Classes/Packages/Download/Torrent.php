<?php

namespace Classes\Packages\Download;

class Torrent implements \JsonSerializable
{
    /**
     * File extension.
     *
     * @var string
     */
    const FILE_EXTENSION = '.torrent';

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
        $this->name = $this->cleanFileName(ltrim($name, DIRECTORY_SEPARATOR));
        $filePath   = $this->path . $this->name . self::FILE_EXTENSION;
        if ($this->is($content) && file_put_contents($filePath, $content, LOCK_EX)) {
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
        $this->name = $this->cleanFileName(ltrim($name, DIRECTORY_SEPARATOR));
        $filePath   = $this->path . $this->name . self::FILE_EXTENSION;
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
        return (self::$urlPrefix . $this->name . self::FILE_EXTENSION);
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        unset($vars['path']);

        return (array_merge($vars, ['url' => $this->getFileUrl()]));
    }

    /**
     * Is torrent file.
     *
     * @param string $content
     * @return bool
     */
    public function is($content)
    {
        $sign = substr($content, 0, 11);
        return (in_array($sign, ['d8:announce', 'd10:created', 'd13:creatio', 'd13:announc', 'd12:_info_l'])
            || substr($sign, 0, 10) == 'd7:comment'
            || substr($sign, 0, 7) == 'd4:info'
            || substr($sign, 0, 3) == 'd9:');
    }

    /**
     * Decode torrent file.
     *
     * @param string $content
     * @return mixed
     */
    public function decode($content)
    {
        $pos = 0;
        return ($this->read($content, $pos));
    }

    /**
     * Get safe clean file name.
     *
     * @param string $fileName
     * @return string
     */
    protected function cleanFileName($fileName)
    {
        return (strtr(
            mb_convert_encoding($fileName, 'ASCII'),
            ' ,;:?*#!§$%&/(){}<>=`´|\\\'"',
            '____________________________'
        ));
    }

    /**
     * Read torrent file to decode torrent format.
     *
     * @param string $content
     * @param int    $pos
     * @return mixed
     */
    protected function read($content, &$pos)
    {
        $length = strlen($content);
        if ($pos < 0 || $pos >= $length) {
            return (null);
        }

        switch ($content{$pos}) {
            case ('i'):
                $pos++;
                $nLength = strspn($content, '-0123456789', $pos);
                $posLeft = $pos;

                $pos += $nLength;
                if ($pos >= $length || $content{$pos} != 'e') {
                    return (null);
                }

                $pos++;
                return (intval(substr($content, $posLeft, $nLength)));

            case ('d'):
                $pos++;

                $info = [];
                while ($pos < $length) {
                    if ($content{$pos} == 'e') {
                        $pos++;
                        return ($info);
                    }

                    if (($key = $this->read($content, $pos)) === null
                        || ($value = $this->read($content, $pos)) === null
                    ) {
                        break;
                    }

                    if (!is_array($key)) {
                        $info[$key] = $value;
                    }
                }
                return (null);

            case ('l'):
                $pos++;

                $info = [];
                while ($pos < $length) {
                    if ($content{$pos} == 'e') {
                        $pos++;
                        return ($info);
                    }

                    if (($value = $this->read($content, $pos)) === null) {
                        break;
                    }

                    $info[] = $value;
                }
                return (null);

            default:
                $nLength = strspn($content, '0123456789', $pos);
                $posLeft = $pos;

                $pos += $nLength;
                if ($pos >= $length || $content{$pos} != ':') {
                    return (null);
                }

                $vLength = intval(substr($content, $posLeft, $nLength));
                $pos++;

                if (strlen($value = substr($content, $pos, $vLength)) != $vLength) {
                    return (null);
                }

                $pos += $vLength;
                return ($value);
        }
    }
}
