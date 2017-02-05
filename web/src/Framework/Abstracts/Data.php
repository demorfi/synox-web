<?php

namespace Framework\Abstracts;

abstract class Data
{
    /**
     * @var array
     */
    protected $array = [];

    /**
     * Get value.
     *
     * @param string $name Name key
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->array[$name]) ? $this->array[$name] : null);
    }

    /**
     * Set value.
     *
     * @param string $name Name key
     * @param mixed $value Value key
     * @return void
     */
    public function __set($name, $value)
    {
        $this->array[$name] = $value;
    }

    /**
     * Isset key.
     *
     * @param string $name Name key
     * @return bool
     */
    public function __isset($name)
    {
        return (isset($this->array[$name]));
    }

    /**
     * Get value.
     *
     * @param string $name Name key
     * @param mixed $default If request key not found it return default value
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return ((isset($this->array[$name]) && !empty($this->array[$name])) ? $this->array[$name] : $default);
    }

    /**
     * Has key.
     *
     * @param string $name Name key
     * @return bool
     */
    public function has($name)
    {
        return (isset($this->array[$name]));
    }

    /**
     * Slice array by key.
     *
     * @param string $prefix
     * @param callable|null $callable
     * @return array
     */
    public function slice($prefix, callable $callable = null)
    {
        $array = [];
        foreach ($this->array as $key => $value) {
            if (($pos = stripos($key, $prefix)) !== false && (is_null($callable) || $callable($key, $value))) {
                $array[substr($key, ($pos + strlen($prefix)))] = $value;
            }
        }

        return ($array);
    }

    /**
     * Get values.
     *
     * @return array
     */
    public function getAll()
    {
        return ($this->array);
    }

    /**
     * Get keys.
     *
     * @return array
     */
    public function getKeys()
    {
        return (array_keys($this->array));
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function size()
    {
        return (sizeof($this->array));
    }

    /**
     * Flush values.
     */
    public function flush()
    {
        $this->array = [];
    }
}
