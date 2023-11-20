<?php declare(strict_types=1);

namespace App\Components;

use App\Package\Enums\FileExtension;
use Digua\Components\{DataFile, Types};
use Digua\Config as ConfigBase;
use Digua\Exceptions\{Path as PathException, Storage as StorageException};

class Config extends ConfigBase
{
    /**
     * @var DataFile
     */
    private DataFile $storage;

    /**
     * @inheritdoc
     * @throws PathException
     * @throws StorageException
     * @uses DataFile
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->storage = DataFile::create($name . FileExtension::CONFIG->value);
        $this->overwrite($this->collection()->merge($this->storage->read())->toArray());
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return bool
     * @throws StorageException
     */
    public function update(string $name, mixed $value): bool
    {
        $type  = Types::value($this->get($name))->getNameShort();
        $value = Types::value($value)->to($type)->getValue();
        $this->storage->set($name, $value);
        return $this->storage->save();
    }
}