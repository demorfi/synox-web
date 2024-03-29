<?php declare(strict_types=1);

namespace App\Components;

use Digua\Exceptions\Path as PathException;
use SplFileInfo;

readonly class File
{
    /**
     * @param SplFileInfo $fileInfo
     * @throws PathException
     */
    public function __construct(protected SplFileInfo $fileInfo)
    {
        if (!$this->isExist()) {
            throw new PathException(sprintf('The file (%s) is not exist!', $this->getPath()));
        }
    }

    /**
     * @param string $filePath
     * @return static
     * @throws PathException
     */
    public static function make(string $filePath): static
    {
        return new static(new SplFileInfo($filePath));
    }

    /**
     * @return string[]
     */
    public function __serialize(): array
    {
        return [$this->getPath()];
    }

    /**
     * @param array $data
     * @return void
     * @throws PathException
     */
    public function __unserialize(array $data): void
    {
        $this->__construct(new SplFileInfo(...$data));
    }

    /**
     * @return bool
     */
    public function isExist(): bool
    {
        return $this->fileInfo->isFile();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->fileInfo->getPathname();
    }

    /**
     * @return string
     */
    public function getBasename(): string
    {
        return $this->fileInfo->getFilename();
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileInfo->getSize();
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return file_get_contents($this->fileInfo->getPathname());
    }
}