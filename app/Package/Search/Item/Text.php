<?php declare(strict_types=1);

namespace App\Package\Search\Item;

use App\Package\Search\Abstracts\Item;

class Text extends Item
{
    /**
     * @var ?string
     */
    protected ?string $description = null;

    /**
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $text
     * @return void
     */
    public function setDescription(string $text): void
    {
        $this->description = $text;
    }
}