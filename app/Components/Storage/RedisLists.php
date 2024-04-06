<?php declare(strict_types=1);

namespace App\Components\Storage;

use App\Components\Config;
use Digua\Interfaces\Storage;
use Digua\Exceptions\Storage as StorageException;
use Redis;
use RedisException;
use Exception;

class RedisLists implements Storage
{
    /**
     * @var int
     */
    private static int $expiration = 0;

    /**
     * @var ?Redis
     */
    private static ?Redis $connection = null;

    /**
     * @param string $name
     * @throws StorageException
     */
    public function __construct(private readonly string $name)
    {
        $this->connect(new Config('redis'));
    }

    /**
     * @param Config $config
     * @return void
     * @throws StorageException
     */
    protected function connect(Config $config): void
    {
        try {
            if (!self::$connection?->isConnected()) {
                self::$connection = new Redis;
                self::$connection->connect(
                    (string)$config->get('host', 'localhost'),
                    (int)$config->get('port', 6379),
                    (float)$config->get('timeout', 1.5),
                    null,
                    0,
                    (float)$config->get('timeout', 1.5)
                );
                self::$connection->auth($config->get('pass'));
                self::$expiration = (int)$config->get('expire', 0);
            }
        } catch (Exception $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     * @throws RedisException
     */
    public function getPath(): string
    {
        return sprintf('%s:%d', self::$connection?->getHost(), self::$connection?->getPort());
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     * @throws RedisException
     */
    public function read(): ?array
    {
        return self::$connection?->lRange($this->name, 0, -1) ?: null;
    }

    /**
     * @inheritdoc
     * @throws RedisException
     */
    public function write(string $data): bool
    {
        $oldLength = (int)self::$connection?->lLen($this->name);
        $newLength = self::$connection?->rPush($this->name, $data);

        if (!$oldLength && $newLength >= 1 && self::$expiration > 0) {
            self::$connection->expire($this->name, self::$expiration);
        }

        return $newLength > $oldLength;
    }

    /**
     * @inheritdoc
     * @throws RedisException
     */
    public function rewrite(callable|string $data): bool
    {
        $this->free();
        return $this->write(is_callable($data) ? $data($this->read()) : $data);
    }

    /**
     * @inheritdoc
     * @throws RedisException
     */
    public function free(): bool
    {
        return self::$connection?->del($this->name) > 0;
    }

    /**
     * @inheritdoc
     * @throws RedisException
     */
    public static function has(string $name): bool
    {
        return self::$connection?->lLen($name) > 0;
    }

    /**
     * @inheritdoc
     */
    public function hasEof(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setEof(): bool
    {
        return false;
    }
}