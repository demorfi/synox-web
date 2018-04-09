<?php

namespace Framework;

class Storage extends Abstracts\Data implements \JsonSerializable
{
    /**
     * Path to storage.
     *
     * @var string
     */
    const PATH = APP_PATH . '/Storage/';

    /**
     * Original data for diff.
     *
     * @var array
     */
    private $original = [];

    /**
     * Storage name.
     *
     * @var string
     */
    private $name;

    /**
     * Storage constructor.
     *
     * @param string $name Storage name
     */
    public function __construct($name)
    {
        $this->name = $name . '.json';
        if (file_exists(static::PATH . $this->name)) {
            $this->array = $this->original = json_decode(file_get_contents(static::PATH . $this->name), true);
        }
    }

    /**
     * Load storage.
     *
     * @param string $name Storage name
     * @return Storage
     */
    public static function load($name)
    {
        return (new self($name));
    }

    /**
     * Save storage.
     *
     * @inheritdoc
     */
    public function __destruct()
    {
        if (sizeof($this->array) != sizeof($this->original)
            || sizeof(@array_diff_assoc($this->array, $this->original))
        ) {
            file_put_contents(static::PATH . $this->name, json_encode($this->array));
        }
    }

    /**
     * Save storage.
     *
     * @return void
     */
    public function save()
    {
        file_put_contents(static::PATH . $this->name, json_encode($this->array));
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return ($this->array);
    }
}
