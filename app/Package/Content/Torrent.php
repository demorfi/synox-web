<?php declare(strict_types=1);

namespace App\Package\Content;

use App\Enums\ContentType;

class Torrent extends File
{
    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/public/files/torrents'
    ];

    /**
     * @inheritdoc
     */
    public function getType(): ContentType
    {
        return ContentType::TORRENT;
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