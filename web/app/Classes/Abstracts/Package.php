<?php

namespace Classes\Abstracts;

use Classes\Interfaces\Package as _Package;
use Framework\Storage;

abstract class Package implements _Package
{
    /**
     * Package settings.
     *
     * @var Storage
     */
    private $settings;

    /**
     * Package constructor.
     *
     * @inheritdoc
     */
    public function __construct(Storage $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function __call($name, $arguments)
    {
        return (false);
    }

    /**
     * Get package setting.
     *
     * @param string $name
     * @param mixed $default If request key not found it return default value
     * @return null
     */
    protected function getSetting($name, $default = null)
    {
        return ($this->settings->get($name, $default));
    }

    /**
     * Set package setting.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setSettings($name, $value)
    {
        $this->settings->$name = $value;
    }
}