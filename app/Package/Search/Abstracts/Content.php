<?php declare(strict_types=1);

namespace App\Package\Search\Abstracts;

use App\Package\Search\{Interfaces\Content as PackageContentInterface, Enums\Subtype};

abstract class Content implements PackageContentInterface
{
    /**
     * @var ?string
     */
    protected ?string $type = null;

    /**
     * @var ?string
     */
    protected ?string $extension = null;

    /**
     * @var ?string
     */
    protected ?string $typeId = null;

    /**
     * @var ?string
     */
    protected ?string $content = null;

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
    public function get(): ?string
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
    abstract public function getType(): Subtype;

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $vars = get_object_vars($this);
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $vars)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize());
    }
}