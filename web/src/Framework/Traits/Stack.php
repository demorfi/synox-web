<?php

namespace Framework\Traits;

use Framework\Memory;

trait Stack
{
    /**
     * Stack size.
     *
     * @var int
     */
    protected $defaultSize = 1024;

    /**
     * Memory instance.
     *
     * @var Memory
     */
    protected $memory;

    /**
     * Stack constructor.
     *
     * @param string $hash
     */
    public function __construct($hash = null)
    {
        if (!is_null($hash)) {
            $size = (isset($this->size)
                ? $this->size
                : $this->defaultSize);

            $this->memory = Memory::restore($hash . ':' . $size);
        } else {
            $this->memory = Memory::create($this->size);
        }
    }

    /**
     * Get memory hash.
     *
     * @return string
     */
    public function getHash()
    {
        $size = (isset($this->size)
            ? $this->size
            : $this->defaultSize);
        return (preg_replace('/:' . $size . '$/', '', $this->memory->getHash()));
    }

    /**
     * Set finished flag.
     *
     * @return void
     */
    public function setEndFlag()
    {
        $this->memory->push(-1);
    }

    /**
     * Is finished flag.
     *
     * @param mixed $data
     * @return bool
     */
    public function isEndFlag($data)
    {
        return ($data === -1);
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        return (call_user_func_array([$this->memory, $name], $arguments));
    }
}
