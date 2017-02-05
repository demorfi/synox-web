<?php

namespace Classes\Packages;

class PackageList extends \ArrayIterator
{
    /**
     * List type.
     *
     * @var string
     */
    public $type;

    /**
     * Set list type.
     *
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get list type.
     *
     * @return string
     */
    public function getType()
    {
        return ($this->type);
    }

    /**
     * Get list by types.
     *
     * @param array $types
     * @return self
     */
    public function getByTypes(array $types)
    {
        $instance = new self;
        foreach ($types as $type) {
            $subInstance = new self($this->getByType($type));
            $subInstance->setType($type);
            $instance->append($subInstance);
        }

        return ($instance);
    }

    /**
     * Get list by enabled packages.
     *
     * @return self
     */
    public function getByEnabled()
    {
        $instance = new self;
        foreach ($this->getArrayCopy() as $item) {
            if ($item instanceof Package && $item->isEnabled()) {
                $instance->append($item);
            }
        }

        return ($instance);
    }

    /**
     * Get list by type.
     *
     * @param string $type
     * @return self
     */
    public function getByType($type)
    {
        $instance = new self;
        foreach ($this->getArrayCopy() as $item) {
            if ($item instanceof Package && $item->getType() == $type) {
                $instance->append($item);
            }
        }

        $instance->setType($type);
        return ($instance);
    }

    /**
     * Find package.
     *
     * @param string $id
     * @return Package|null
     */
    public function find($id)
    {
        foreach ($this->getArrayCopy() as $item) {
            if ($item instanceof Package && $item->getId() == $id) {
                return ($item);
            }
        }

        return (null);
    }
}