<?php declare(strict_types=1);

namespace App\Components;

use Digua\Components\DataFile;
use Digua\Enums\FileExtension;
use Digua\Exceptions\Storage as StorageException;

class Settings extends DataFile
{
    /**
     * @param string $fileName
     * @throws StorageException
     */
    public function __construct(string $fileName)
    {
        parent::__construct($fileName);
        $this->read();
    }

    /**
     * @return string
     */
    final public function getId(): string
    {
        return strtolower(basename($this->getName(), FileExtension::JSON->value));
    }
}