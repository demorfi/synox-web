<?php

namespace Classes\Interfaces;

use Framework\Storage;

interface Package
{
    /**
     * Package constructor.
     *
     * @param Storage $settings Package settings
     */
    public function __construct(Storage $settings);

    /**
     * Get package name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get short description package.
     *
     * @return string
     */
    public function getShortDescription();

    /**
     * Get package version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Has auth of package.
     *
     * @return bool
     */
    public function hasAuth();
}
