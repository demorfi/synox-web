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
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getShortDescription();

    /**
     * @return bool
     */
    public function hasAuth();
}