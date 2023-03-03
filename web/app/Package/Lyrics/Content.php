<?php declare(strict_types=1);

namespace App\Package\Lyrics;

use JsonSerializable;

class Content implements JsonSerializable
{
    /**
     * @var string
     */
    private string $content;

    /**
     * @var bool
     */
    private bool $available = false;

    /**
     * Add content.
     *
     * @param string $string
     * @return void
     */
    public function add(string $string): void
    {
        $this->content   = nl2br(trim(strip_tags($string)));
        $this->available = strlen($this->content) > 0;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
