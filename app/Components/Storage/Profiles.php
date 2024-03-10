<?php declare(strict_types=1);

namespace App\Components\Storage;

use App\Package\Search\Profile;
use Digua\Components\{ArrayCollection, DataFile};
use Digua\Exceptions\Storage as StorageException;

class Profiles
{
    /**
     * @var string
     */
    protected static string $fileName = 'profiles';

    /**
     * @var DataFile
     */
    protected readonly DataFile $storage;

    /**
     * @throws StorageException
     */
    public function __construct()
    {
        $this->storage = new DataFile(static::$fileName);
        $this->storage->read();
    }

    /**
     * @return static
     */
    public static function load(): static
    {
        return new static();
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getFromHash(string $id): string
    {
        $id = strtolower($id);
        return preg_match('/[a-z0-9]{32}/', $id) === 1 ? $id : md5($id);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->storage->getValues();
    }

    /**
     * @param string $id
     * @return ?array
     */
    public function get(string $id): ?array
    {
        return $this->storage->get($this->getFromHash($id));
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->storage->has($this->getFromHash($id));
    }

    /**
     * @param Profile $profile
     * @param ?string $id
     * @return array|bool
     * @throws StorageException
     */
    public function add(Profile $profile, ?string $id): array|bool
    {
        if ($profile->isEmpty()) {
            return false;
        }

        if (empty($id)) {
            do {
                $id = (string)rand();
            } while ($this->has($id));
        }

        $this->storage->set($this->getFromHash($id), ['id' => $id, 'values' => $profile]);
        return $this->storage->save() ? $this->get($id) : false;
    }

    /**
     * @param string $id
     * @return bool
     * @throws StorageException
     */
    public function remove(string $id): bool
    {
        $this->storage->remove($this->getFromHash($id));
        return $this->storage->save();
    }

    /**
     * @param string $id
     * @return ArrayCollection
     */
    public function collection(string $id): ArrayCollection
    {
        return ArrayCollection::make($this->get($id) ?: [])->collapse('values');
    }
}