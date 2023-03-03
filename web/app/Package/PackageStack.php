<?php declare(strict_types=1);

namespace App\Package;

use App\Components\Helper;
use Digua\Components\{Stack, Storage};
use Digua\Exceptions\Storage as StorageException;

class PackageStack extends Stack
{
    /**
     * @var int
     */
    private int $size = 5242860;

    /**
     * @param string $name
     * @throws StorageException
     */
    public function __construct(string $name)
    {
        parent::__construct(Storage::makeSharedMemory($name, $this->size));
    }

    /**
     * @return string
     */
    public static function makeHash(): string
    {
        return (string)Helper::makeIntHash();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->getName();
    }
}