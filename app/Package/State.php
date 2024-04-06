<?php declare(strict_types=1);

namespace App\Package;

use App\Components\Storage\DiskFile;
use App\Package\Exceptions\State as StateException;
use Digua\Components\{DataFile, Storage, Types};
use Digua\Enums\FileExtension;
use Digua\Exceptions\Storage as StorageException;
use Throwable;

class State extends DataFile
{
    /**
     * @var Settings
     */
    readonly private Settings $settings;

    /**
     * @var Adapter
     */
    readonly private Adapter $adapter;

    /**
     * @param Source $source
     * @throws StorageException
     */
    public function __construct(readonly protected Source $source)
    {
        parent::__construct($this->getId());
        $this->read();

        $this->settings = Settings::create($this->getId());
        $this->settings->read();
    }

    /**
     * @return void
     * @throws StorageException
     */
    protected function init(): void
    {
        $this->storage = Storage::make(
            DiskFile::class,
            $this->fileName . FileExtension::JDB->value,
            ROOT_PATH . '/storage/states'
        );
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return strtolower($this->source->getType()->getId() . '@' . $this->source->getClassName());
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @return bool
     */
    public function isValidState(): bool
    {
        return $this->getFixedTypeValue('valid', 'bool', false);
    }

    /**
     * @return bool
     */
    public function isIgnoreState(): bool
    {
        return $this->getFixedTypeValue('ignore', 'bool', false);
    }

    /**
     * @param bool $isValid
     * @return bool
     */
    private function setValidState(bool $isValid): bool
    {
        $this->setStateValue('valid', $isValid);
        $this->setStateValue('size', $this->source->getFile()->getFileSize());
        return $this->save();
    }

    /**
     * @return bool
     */
    public function resetValidState(): bool
    {
        $this->setStateValue('valid', false);
        $this->setStateValue('size', -1);
        return $this->save();
    }

    /**
     * @param bool $isIgnore
     * @return bool
     */
    public function setIgnoreState(bool $isIgnore): bool
    {
        $this->setStateValue('ignore', $isIgnore);
        return $this->save();
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function setStateValue(string $key, mixed $value): void
    {
        if ($this->has($key)) {
            $type  = Types::value($this->get($key))->getNameShort();
            $value = Types::value($value)->to($type)->getValue();
        }

        $this->set($key, $value);
    }

    /**
     * @param bool $force
     * @return ?Adapter
     * @throws StateException
     */
    public function getAdapter(bool $force = false): ?Adapter
    {
        if (!isset($this->adapter)) {
            try {
                if (!$force && $this->isIgnoreState()) {
                    return null;
                }

                $validSize = $this->getFixedTypeValue('size', 'int', -1);
                if (!$this->isValidState() || !$this->source->isValidFileSize($validSize)) {
                    // Skip endless validation if the size remains unchanged
                    if ($validSize >= 0 && $this->source->isValidFileSize($validSize)) {
                        return null;
                    }

                    if (!$this->validate()) {
                        throw new StateException(sprintf('Package (%s) is invalid!', $this->source->getClassName()));
                    }
                }

                $this->adapter = new Adapter(
                    $this->source->getType()->makeRelay($this->source->tryNewInstance($this->settings), $this),
                );
            } catch (Throwable $e) {
                if ($this->isValidState()) {
                    $this->setValidState(false);
                }
                throw new StateException($e->getMessage());
            }
        }

        return $this->adapter;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $isValid = $this->source->isValid();
        return $isValid && $this->setValidState(true);
    }
}