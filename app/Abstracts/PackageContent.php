<?php declare(strict_types=1);

namespace App\Abstracts;

use App\Interfaces\PackageContent as PackageContentInterface;
use App\Enums\ContentType;

abstract class PackageContent implements PackageContentInterface
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
     * @return ContentType
     */
    abstract public function getType(): ContentType;

    /**
     * @inheritdoc
     */
    public function add(string $content): void
    {
        $this->content   = $content;
        $this->available = $this->is($this->content);
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
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}