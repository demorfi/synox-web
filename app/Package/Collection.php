<?php declare(strict_types=1);

namespace App\Package;

use App\Package\Enums\Type;
use Digua\Components\ArrayCollection;

class Collection extends ArrayCollection
{
    /**
     * @var ?Type
     */
    public ?Type $type = null;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'type'     => $this->type?->getName(),
            'packages' => $this->array
        ];
    }

    /**
     * @param ?Type $type
     * @return void
     */
    public function setType(?Type $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ?Type
     */
    public function getType(): ?Type
    {
        return $this->type;
    }

    /**
     * @param callable $callable
     * @return self
     */
    public function filterByType(callable $callable): static
    {
        $instance = new static;
        foreach ($this->array as $item) {
            if ($item instanceof Adapter && $callable($item)) {
                $instance->append($item);
                continue;
            }

            if ($item instanceof static) {
                foreach ($item->filterByType($callable) as $subItem) {
                    $instance->append($subItem);
                }
            }
        }

        $instance->setType($this->getType());
        return $instance;
    }

    /**
     * @return static
     */
    public function getByEnabled(): static
    {
        return $this->filterByType(fn($item) => $item->isEnabled());
    }

    /**
     * @return static
     */
    public function getByAvailable(): static
    {
        return $this->filterByType(fn($item) => $item->isAvailable());
    }

    /**
     * @param Type ...$types
     * @return self
     */
    public function getByType(Type ...$types): static
    {
        $instance = new static;
        foreach ($types as $type) {
            $subInstance = $this->filterByType(fn($item) => $item->getType() == $type);
            $subInstance->setType($type);
            $instance->append($subInstance);
        }

        return sizeof($types) <> 1 ? $instance : $instance->offsetGet(0);
    }

    /**
     * @param string $id
     * @return ?Adapter
     */
    public function find(string $id): ?Adapter
    {
        foreach ($this->array as $item) {
            if ($item instanceof Adapter && $item->getId() == $id) {
                return $item;
            }

            if ($item instanceof self && ($subItem = $item->find($id)) instanceof Adapter) {
                return $subItem;
            }
        }

        return null;
    }
}