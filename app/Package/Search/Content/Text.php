<?php declare(strict_types=1);

namespace App\Package\Search\Content;

use App\Package\Search\{Abstracts\File, Enums\Type};

class Text extends File
{
    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/public/files'
    ];

    /**
     * @inheritdoc
     */
    public function getType(): Type
    {
        return Type::TEXT;
    }

    /**
     * @inheritdoc
     */
    public function create(string $name, string $content): static
    {
        $this->add($content);
        if ($this->isAvailable()) {
            parent::create($name, $this->content);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function add(string $content): void
    {
        parent::add(trim(strip_tags($content)));
    }
}