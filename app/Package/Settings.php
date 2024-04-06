<?php declare(strict_types=1);

namespace App\Package;

use App\Components\Storage\{DiskFile, Fake};
use Digua\Components\{DataFile, Storage};
use Digua\Enums\FileExtension;
use Digua\Exceptions\Storage as StorageException;

class Settings extends DataFile
{
    /**
     * @param string $fileName
     * @param string $type
     * @throws StorageException
     */
    protected function __construct(string $fileName, private readonly string $type)
    {
        parent::__construct($fileName);
        $this->read();
    }

    /**
     * @return void
     * @throws StorageException
     */
    final protected function init(): void
    {
        $this->storage = Storage::make(
            $this->type,
            $this->fileName . FileExtension::JDB->value,
            ROOT_PATH . '/storage/settings'
        );
    }

    /**
     * @inheritdoc
     */
    public static function create(string $fileName): static
    {
        return new static($fileName, DiskFile::class);
    }

    /**
     * @param string $fileName
     * @return static
     * @throws StorageException
     */
    public static function fake(string $fileName): static
    {
        return new static($fileName, Fake::class);
    }

    /**
     * @return string
     */
    final public function getId(): string
    {
        return $this->fileName;
    }
}