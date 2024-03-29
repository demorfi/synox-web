<?php declare(strict_types=1);

namespace App\Components\Storage;

use App\Components\Helper;
use Digua\Traits\DiskPath;
use Digua\Interfaces\Storage as StorageInterface;

class Fake implements StorageInterface
{
    use DiskPath;

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/storage'
    ];

    /**
     * @var string
     */
    protected string $filePath;

    /**
     * @param string $name Storage file name
     */
    public function __construct(private readonly string $name)
    {
        $this->filePath = self::getDiskPath(Helper::filterFileName($this->name));
    }

    /**
     * @param string $name
     * @return self
     */
    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return true;
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
     */
    public function getPath(): string
    {
        return $this->filePath;
    }

    /**
     * @inheritdoc
     */
    public function read(): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function write(string $data): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rewrite(string|callable $data): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function free(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasEof(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setEof(): bool
    {
        return true;
    }
}