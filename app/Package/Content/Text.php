<?php declare(strict_types=1);

namespace App\Package\Content;

use App\Enums\ContentType;

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
    public function getType(): ContentType
    {
        return ContentType::TEXT;
    }

    /**
     * @inheritdoc
     */
    public function create(string $name, string $content): self
    {
        $this->add($content);
        if ($this->isAvailable()) {
            parent::create($name, $this->content);
        }

        return $this;
    }

    /**
     * @param string $content
     * @return void
     */
    public function add(string $content): void
    {
        parent::add(trim(strip_tags($content)));
    }
}