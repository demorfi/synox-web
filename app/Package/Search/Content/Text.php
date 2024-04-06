<?php declare(strict_types=1);

namespace App\Package\Search\Content;

use App\Package\Search\Enums\Subtype;

class Text extends Base
{
    /**
     * @inheritdoc
     */
    public function getType(): Subtype
    {
        return Subtype::TEXT;
    }

    /**
     * @inheritdoc
     */
    public function create(string $name, string $content): static
    {
        $this->set($content);
        parent::create($name, $this->content);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function set(string $content): void
    {
        parent::set(trim(strip_tags($content)));
    }
}