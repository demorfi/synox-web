<?php declare(strict_types=1);

namespace App\Package\Search\Content;

use App\Package\Search\Enums\Type;

class Torrent extends Base
{
    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/public/files/torrents'
    ];

    /**
     * @var ?string
     */
    protected ?string $hash = null;

    /**
     * @var ?string
     */
    protected ?string $magnet = null;

    /**
     * @inheritdoc
     */
    public function getType(): Type
    {
        return Type::TORRENT;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        return [...$data, 'content' => null];
    }

    /**
     * @param string $hash
     * @param string $type
     * @return void
     */
    public function setHash(string $hash, string $type = 'btih'): void
    {
        $this->hash   = strtoupper($hash);
        $this->magnet = sprintf('magnet:?xt=urn:%s:%s', $type, $hash);
    }

    /**
     * @return ?string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $magnet
     * @return void
     */
    public function setMagnet(string $magnet): void
    {
        preg_match('/urn:(tree:tiger|sha1|bitprint|ed2k|aich|kzhash|btih|md5|crc32):(?P<hash>\w+):?/', $magnet, $result);
        if (isset($result['hash'])) {
            $this->magnet = $magnet;
            $this->hash   = strtoupper($result['hash']);
        }
    }

    /**
     * @return ?string
     */
    public function getMagnet(): ?string
    {
        return $this->magnet;
    }

    /**
     * @param string $content
     * @param int    $pos
     * @return string|array|int|null
     */
    protected function decode(string $content, int &$pos = 0): string|array|int|null
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
                        $info['isDct'] = true;
                        $pos++;
                        return $info;
                    }

                    if (($key = $this->decode($content, $pos)) === null
                        || ($value = $this->decode($content, $pos)) === null
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

                    if (($value = $this->decode($content, $pos)) === null) {
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

    /**
     * @param mixed $info
     * @return ?string
     */
    protected function encode(mixed $info): ?string
    {
        if (is_array($info)) {
            $dict = isset($info['isDct']) && $info['isDct'] === true && ksort($info, SORT_STRING);
            $line = $dict ? 'd' : 'l';
            foreach ($info as $key => $value) {
                if ($dict) {
                    if ($key == 'isDct' && is_bool($value)) {
                        continue;
                    }
                    $line .= strlen($key) . ':' . $key;
                }

                $line .= match (true) {
                    is_int($value) || is_float($value) => "i{$value}e",
                    is_string($value) => strlen($value) . ':' . $value,
                    default => $this->encode($value)
                };
            }

            return $line . 'e';
        }

        return is_string($info)
            ? (strlen($info) . ':' . $info)
            : (is_numeric($info)
                ? "i{$info}e"
                : null);
    }

    /**
     * @param string $content
     * @return ?array
     */
    public function read(string $content): ?array
    {
        $data = $this->decode($content);
        if (isset($data['info']) && is_array($data['info']) && ($encoded = $this->encode($data['info'])) !== null) {
            $data['hash']   = strtoupper(sha1($encoded));
            $data['magnet'] = 'magnet:?xt=urn:btih:' . $data['hash'];
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function create(string $name, string $content): static
    {
        if ($this->is($content) && !empty($torrent = $this->read($content))) {
            if (isset($torrent['hash'], $torrent['info']['name'])) {
                $name = $name ?: $torrent['info']['name'] . '-' . $torrent['hash'];
                $this->setHash($torrent['hash']);
                parent::create($name, $content);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fetch(): ?string
    {
        if (parent::fetch() !== null) {
            if ($this->is($this->content) && !empty($torrent = $this->read($this->content))) {
                if (isset($torrent['hash'])) {
                    $this->setHash($torrent['hash']);
                }
            }
            return $this->content;
        }
        return null;
    }
}