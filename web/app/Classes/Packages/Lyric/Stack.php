<?php

namespace Classes\Packages\Lyric;

use Framework\Traits\Stack as _Stack;

class Stack
{
    use _Stack;

    /**
     * Memory size.
     *
     * @var int
     */
    protected $size = 5242860;

    /**
     * Add new item.
     *
     * @param Item $item
     */
    public function push(Item $item)
    {
        $this->memory->push($item);
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        return (call_user_func_array([$this->memory, $name], $arguments));
    }
}
