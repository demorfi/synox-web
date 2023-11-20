<?php declare(strict_types=1);

namespace App\Package\Search\Item;

use App\Package\Search\Abstracts\Item;

class Text extends Item
{
    /**
     * @var ?string
     */
    protected ?string $content = null;

    /**
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}