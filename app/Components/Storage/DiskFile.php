<?php declare(strict_types=1);

namespace App\Components\Storage;

use Digua\Components\Storage\DiskFile as DiskFileStorage;
use Digua\Exceptions\Path as PathException;

class DiskFile extends DiskFileStorage
{
    /**
     * @var array
     */
    protected static array $config = [];

    /**
     * @param string  $name
     * @param ?string $diskPath
     * @throws PathException
     */
    public function __construct(private readonly string $name, ?string $diskPath = null)
    {
        $diskPath && self::setDiskPath($diskPath);
        parent::__construct($this->name);
    }
}