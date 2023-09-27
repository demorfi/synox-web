<?php declare(strict_types=1);

namespace App\Package\Item;

use App\Abstracts\PackageItem;

class Text extends PackageItem
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