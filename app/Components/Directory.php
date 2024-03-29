<?php declare(strict_types=1);

namespace App\Components;

use Digua\Exceptions\Path as PathException;
use DirectoryIterator;

readonly class Directory
{
    /**
     * @param string $path
     * @throws PathException
     */
    public function __construct(protected string $path)
    {
        if (!is_dir($this->path)) {
            throw new PathException(sprintf('The path (%s) is not readable!', $path));
        }
    }

    /**
     * @param callable $callable
     * @return void
     * @throws PathException
     */
    public function each(callable $callable): void
    {
        $dir = new DirectoryIterator($this->path);
        foreach ($dir as $file) {
            if (!$file->isDot() && $file->isFile()) {
                $callable(new File($file->getFileInfo()));
            }
        }
    }
}