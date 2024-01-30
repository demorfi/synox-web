<?php declare(strict_types=1);

namespace App\Package\Search\Abstracts;

use App\Package\Search\{Interfaces\Content as PackageContentInterface, Enums\Type};

abstract class Content implements PackageContentInterface
{
    /**
     * @var string
     */
    protected readonly string $type;

    /**
     * @var string
     */
    protected readonly string $extension;

    /**
     * @var string
     */
    protected readonly string $typeId;

    /**
     * @var string
     */
    protected string $content;

    /**
     * @var bool
     */
    protected bool $available = false;

    public function __construct()
    {
        $this->type      = $this->getType()->getName();
        $this->typeId    = $this->getType()->getId();
        $this->extension = $this->getType()->extension()->value;
    }

    /**
     * @inheritdoc
     */
    public function set(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function is(string $content): bool
    {
        return strlen($content) > 0;
    }

    /**
     * @inheritdoc
     */
    public function get(): string
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @inheritdoc
     */
    abstract public function getType(): Type;

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}