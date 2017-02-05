<?php

namespace Framework\Traits;

trait Output
{
    /**
     * Start buffer.
     */
    public function startBuffer()
    {
        return (ob_start());
    }

    /**
     * Get buffer content.
     *
     * @return string
     */
    public function flushBuffer()
    {
        return (ob_get_clean());
    }

    /**
     * Clean buffer.
     */
    public function cleanBuffer()
    {
        ob_clean();
    }
}