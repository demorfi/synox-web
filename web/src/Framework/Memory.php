<?php

namespace Framework;

class Memory implements \JsonSerializable
{
    /**
     * Memory size.
     *
     * @var number
     */
    private $size;

    /**
     * Semaphore key.
     *
     * @var int
     */
    private $semKey;

    /**
     * Shared memory key.
     *
     * @var int
     */
    private $shmKey;

    /**
     * Semaphore id.
     *
     * @var resource
     */
    private $semId;

    /**
     * Shared memory id.
     *
     * @var resource
     */
    private $shmId;

    /**
     * Offset key.
     *
     * @var int
     */
    private $offset = 1;

    /**
     * Memory constructor.
     *
     * @param int $semKey
     * @param int $shmKey
     * @param int $size
     */
    public function __construct($semKey, $shmKey, $size = 1024)
    {
        $this->semKey = (int)$semKey;
        $this->shmKey = (int)$shmKey;
        $this->size   = abs((int)$size);

        $this->attach();
    }

    /**
     * Create memory.
     *
     * @param int $size
     * @return self
     */
    public static function create($size = 1024)
    {
        $semKey = random_int(time(), PHP_INT_MAX);
        $shmKey = random_int(time() + 1, PHP_INT_MAX);

        return (new self($semKey, $shmKey, $size));
    }

    /**
     * Restore memory.
     *
     * @param string $hash
     * @return self
     * @throws \Exception
     */
    public static function restore($hash)
    {
        if (strpos($hash, ':') === false) {
            throw new \Exception('error restore hash!');
        }

        list($semKey, $shmKey, $size) = explode(':', $hash);
        return (new self($semKey, $shmKey, $size));
    }

    /**
     * Attach.
     *
     * @return void
     * @throws \Exception
     */
    protected function attach()
    {
        $this->semId = sem_get($this->semKey, 1);
        if ($this->semId === false) {
            throw new \Exception('error creating semaphore!');
        }

        if (!sem_acquire($this->semId)) {
            sem_remove($this->semId);
            throw new \Exception('error when trying to take a semaphore ' . $this->semId . '!');
        }

        $this->shmId = shm_attach($this->shmKey, $this->size);
        if ($this->shmId === false) {
            throw new \Exception('error when connecting to shared memory!');
        }
    }

    /**
     * Detach memory.
     *
     * @return void
     * @throws \Exception
     */
    public function detach()
    {
        if (!sem_release($this->semId)) {
            throw new \Exception('error while trying to release the semaphore ' . $this->semId . '!');
        }

        if (!shm_remove($this->shmId)) {
            throw new \Exception('error when trying to delete the shared memory segment ' . $this->shmId . '!');
        }

        if (!sem_remove($this->semId)) {
            throw new \Exception('error when attempting to delete the semaphore ' . $this->semId . '!');
        }
    }

    /**
     * Detach memory.
     *
     * @return void
     */
    public function free()
    {
        $this->detach();
    }

    /**
     * Get semaphore key.
     *
     * @return resource
     */
    public function getSemKey()
    {
        return ($this->semId);
    }

    /**
     * Get shared memory key.
     *
     * @return int
     */
    public function getShmKey()
    {
        return ($this->shmKey);
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function jsonSerialize()
    {
        return ($this->__toString());
    }

    /**
     * Get memory hash.
     *
     * @return string
     */
    public function getHash()
    {
        return ($this->__toString());
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function __toString()
    {
        return ($this->semKey . ':' . $this->shmKey . ':' . $this->size);
    }

    /**
     * Push.
     *
     * @param mixed $data
     * @return void
     * @throws \Exception
     */
    public function push($data)
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                if (!shm_put_var($this->shmId, $this->offset, [])) {
                    throw new \Exception();
                }
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            array_push($stack, $data);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            sem_remove($this->semId);
            shm_remove($this->shmId);
            throw new \Exception('error when trying to write to shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Pull.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function pull()
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            if ($stack === false) {
                throw new \Exception();
            }

            if (sizeof($stack) < 1) {
                return (false);
            }

            $data = array_pop($stack);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new \Exception();
            }

            return ($data);
        } catch (\Exception $e) {
            throw new \Exception('error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Shift.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function shift()
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            if ($stack === false) {
                throw new \Exception();
            }

            if (sizeof($stack) < 1) {
                return (false);
            }

            $data = array_shift($stack);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new \Exception();
            }

            return ($data);
        } catch (\Exception $e) {
            throw new \Exception('error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Read.
     *
     * @return bool|\Generator
     */
    public function read()
    {
        while (true) {
            $data = $this->shift();
            if (!$data) {
                return (false);
            }

            yield $data;
        }

        return (false);
    }

    /**
     * Read.
     *
     * @return bool|array
     * @throws \Exception
     */
    public function readToArray()
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            if ($stack === false) {
                throw new \Exception();
            }

            if (sizeof($stack) < 1) {
                return (false);
            }

            return ($stack);
        } catch (\Exception $e) {
            throw new \Exception('error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function size()
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
        } catch (\Exception $e) {
            return (0);
        }

        return ($stack !== false ? sizeof($stack) : 0);
    }
}
