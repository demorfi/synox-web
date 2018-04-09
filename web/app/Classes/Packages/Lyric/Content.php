<?php

namespace Classes\Packages\Lyric;

class Content implements \JsonSerializable
{
    /**
     * Content.
     *
     * @var string
     */
    private $content;

    /**
     * Available content.
     *
     * @var bool
     */
    private $available = false;

    /**
     * Add content.
     *
     * @param string $string
     * @return void
     */
    public function add($string)
    {
        $this->content = $this->filter($string);
        if (strlen($this->content)) {
            $this->available = true;
        }
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function get()
    {
        return ($this->content);
    }

    /**
     * Is available content.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return ($this->available);
    }

    /**
     * Filter content.
     *
     * @param string $string
     * @return string
     */
    public static function filter($string)
    {
        $string = preg_replace('/<br(\ ?\/)?>/', PHP_EOL, $string);
        $string = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $string);
        $string = preg_replace(
            ['/(\n\n\n)+/', '/\n+/', '/\t?/', '/<hr>/'],
            ['<hr>', "\n", '', "\n\n"],
            strip_tags($string)
        );

        return (nl2br(trim($string)));
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        return (get_object_vars($this));
    }
}
