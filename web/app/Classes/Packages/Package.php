<?php

namespace Classes\Packages;

use Classes\Interfaces\Package as _Package;
use Framework\Storage;

class Package
{
    /**
     * @var _Package
     */
    private $package;

    /**
     * Package settings.
     *
     * @var Storage
     */
    private $settings;

    /**
     * Package type.
     *
     * @var string
     */
    private $type;

    /**
     * Package constructor.
     *
     * @param string $type Package type
     * @param _Package $package
     * @param Storage $settings Package settings
     */
    public function __construct($type, _Package $package, Storage $settings)
    {
        $this->type     = $type;
        $this->package  = $package;
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        return (call_user_func_array([$this->package, $name], $arguments));
    }

    /**
     * Get package setting.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return ($this->settings->__get($name));
    }

    /**
     * Set package setting.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->settings->__set($name, $value);
    }

    /**
     * Get package id.
     *
     * @return string
     */
    public function getId()
    {
        $className = get_class($this->package);
        return (substr($className, strrpos($className, '\\') + 1));
    }

    /**
     * Get package type.
     *
     * @return string
     */
    public function getType()
    {
        return ($this->type);
    }

    /**
     * Get package settings.
     *
     * @return Storage
     */
    public function getSettings()
    {
        return ($this->settings);
    }

    /**
     * Is enabled package.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return ((bool)$this->settings->get('enabled', false));
    }
}