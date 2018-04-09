<?php

namespace Classes\Abstracts\Package;

use Classes\Abstracts\Package;

abstract class Item implements \Serializable, \JsonSerializable
{
    /**
     * Package name.
     *
     * @var string
     */
    protected $package;

    /**
     * Package id.
     *
     * @var string
     */
    protected $id;

    /**
     * Item constructor.
     *
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $className     = get_class($package);
        $this->package = $package->getName();
        $this->id      = substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * Get package name.
     *
     * @return string
     */
    public function getPackage()
    {
        return ($this->package);
    }

    /**
     * Get package id.
     *
     * @return string
     */
    public function getId()
    {
        return ($this->id);
    }

    /**
     * Serialize all variables.
     *
     * @inheritdoc
     */
    public function serialize()
    {
        return (serialize(get_object_vars($this)));
    }

    /**
     * UnSerialize all variables.
     *
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        foreach (unserialize($serialized) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (json_encode($this->jsonSerialize()));
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return (get_object_vars($this));
    }

    /**
     * Get formatted size of byte.
     *
     * @param float $size
     * @return string
     */
    protected function getSizeFormat($size)
    {
        $unit = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
        if (!empty($size)) {
            $length = (int)floor(log($size, 1024));
            return (round($size / pow(1024, $length), 2) . $unit[$length]);
        }
        return ('0' . $unit[0]);
    }
}
