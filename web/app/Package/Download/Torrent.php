<?php declare(strict_types=1);

namespace App\Package\Download;

use App\Components\Helper;
use App\Enums\FileExtension;
use Digua\Traits\{Configurable, DiskPath};
use Digua\Exceptions\Path as PathException;
use JsonSerializable;

class Torrent implements JsonSerializable
{
    use Configurable, DiskPath;

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/public/torrents'
    ];

    /**
     * @var bool
     */
    private bool $available = false;

    /**
     * @var ?string
     */
    private ?string $name = null;

    /**
     * @throws PathException
     */
    public function __construct()
    {
        self::throwIsBrokenDiskPath();
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param string $name
     * @param string $content
     * @return self
     */
    public function create(string $name, string $content): self
    {
        if ($this->is($content)) {
            $filePath = self::getDiskPath(Helper::filterFileName($name) . FileExtension::TORRENT->value);
            if (file_put_contents($filePath, $content, LOCK_EX)) {
                $this->name      = basename($filePath, FileExtension::TORRENT->value);
                $this->available = true;
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function open(string $name): self
    {
        $filePath = self::getDiskPath(Helper::filterFileName($name) . FileExtension::TORRENT->value);
        if (is_readable($filePath) && filesize($filePath)) {
            $this->name      = basename($filePath, FileExtension::TORRENT->value);
            $this->available = true;
        }

        return $this;
    }

    /**
     * @return ?string
     */
    public function fetch(): ?string
    {
        if ($this->available && !empty($this->name)) {
            $filePath = self::getDiskPath($this->name . FileExtension::TORRENT->value);
            return file_get_contents($filePath);
        }

        return null;
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return ?string
     */
    public function getFileUrl(): ?string
    {
        if ($this->available && !empty($this->name)) {
            $filePath = self::getDiskPath($this->name . FileExtension::TORRENT->value);
            return '/' . basename(self::getDiskPath()) . '/' . basename($filePath);
        }

        return null;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        unset($vars['path']);

        return array_merge($vars, ['url' => $this->getFileUrl()]);
    }

    /**
     * Is torrent file.
     *
     * @param string $content
     * @return bool
     */
    public function is(string $content): bool
    {
        $sign = substr($content, 0, 11);
        return (in_array($sign, ['d8:announce', 'd10:created', 'd13:creatio', 'd13:announc', 'd12:_info_l'])
            || str_starts_with($sign, 'd7:comment')
            || str_starts_with($sign, 'd4:info')
            || str_starts_with($sign, 'd9:'));
    }

    /**
     * Decode torrent file.
     *
     * @param string $content
     * @return string|array|int|null
     */
    public function decode(string $content): string|array|int|null
    {
        $pos = 0;
        return $this->read($content, $pos);
    }

    /**
     * Read torrent file to decode torrent format.
     *
     * @param string $content
     * @param int    $pos
     * @return string|array|int|null
     */
    protected function read(string $content, int &$pos): string|array|int|null
    {
        $length = strlen($content);
        if ($pos < 0 || $pos >= $length) {
            return null;
        }

        switch ($content[$pos]) {
            case ('i'):
                $pos++;
                $nLength = strspn($content, '-0123456789', $pos);
                $posLeft = $pos;

                $pos += $nLength;
                if ($pos >= $length || $content[$pos] != 'e') {
                    return null;
                }

                $pos++;
                return intval(substr($content, $posLeft, $nLength));

            case ('d'):
                $pos++;

                $info = [];
                while ($pos < $length) {
                    if ($content[$pos] == 'e') {
                        $pos++;
                        return $info;
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
                return null;

            case ('l'):
                $pos++;

                $info = [];
                while ($pos < $length) {
                    if ($content[$pos] == 'e') {
                        $pos++;
                        return $info;
                    }

                    if (($value = $this->read($content, $pos)) === null) {
                        break;
                    }

                    $info[] = $value;
                }
                return null;

            default:
                $nLength = strspn($content, '0123456789', $pos);
                $posLeft = $pos;

                $pos += $nLength;
                if ($pos >= $length || $content[$pos] != ':') {
                    return null;
                }

                $vLength = intval(substr($content, $posLeft, $nLength));
                $pos++;

                if (strlen($value = substr($content, $pos, $vLength)) != $vLength) {
                    return null;
                }

                $pos += $vLength;
                return $value;
        }
    }
}
