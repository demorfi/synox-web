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
    protected DataFile $storage;

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
        $this->load();
    }

    /**
     * @return void
     */
    protected function load(): void
    {
        $this->overwrite($this->collection()->merge($this->storage->read())->toArray());
    }

    /**
     * @param int|string $key
     * @param mixed  $value
     * @return void
     */
    public function set(int|string $key, mixed $value): void
    {
        parent::set($key, $value);
        $this->storage->set($key, $value);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function update(string $key, mixed $value): bool
    {
        if ($this->has($key)) {
            $type  = Types::value($this->get($key))->getNameShort();
            $value = Types::value($value)->to($type)->getValue();
        }

        $this->set($key, $value);
        return $this->storage->save();
    }
}