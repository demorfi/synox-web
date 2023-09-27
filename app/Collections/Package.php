<?php declare(strict_types=1);

namespace App\Collections;

use App\Enums\ItemType;
use App\Package\Adapter;
use ArrayIterator;
use JsonSerializable;

class Package extends ArrayIterator implements JsonSerializable
{
    /**
     * @var ?ItemType
     */
    public ?ItemType $type = null;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'type'     => $this->type?->getName(),
            'packages' => $this->getArrayCopy()
        ];
    }

    /**
     * @param ?ItemType $type
     * @return void
     */
    public function setItemType(?ItemType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ?ItemType
     */
    public function getItemType(): ?ItemType
    {
        return $this->type;
    }

    /**
     * @param callable $filter
     * @return self
     */
    public function filter(callable $filter): self
    {
        $instance = new self;
        foreach ($this->getArrayCopy() as $item) {
            if ($item instanceof Adapter && $filter($item)) {
                $instance->append($item);
                continue;
            }

            if ($item instanceof self) {
                foreach ($item->filter($filter) as $subItem) {
                    $instance->append($subItem);
                }
            }
        }

        $instance->setItemType($this->getItemType());
        return $instance;
    }

    /**
     * @return self
     */
    public function getByEnabled(): self
    {
        return $this->filter(fn($item) => $item->isEnabled());
    }

    /**
     * @param ItemType ...$types
     * @return self
     */
    public function getByType(ItemType ...$types): self
    {
        $instance = new self;
        foreach ($types as $type) {
            $subInstance = $this->filter(fn($item) => $item->getItemType() == $type);
            $subInstance->setItemType($type);
            $instance->append($subInstance);
        }

        return sizeof($types) > 1 ? $instance : $instance->offsetGet(0);
    }

    /**
     * @param string $id
     * @return ?Adapter
     */
    public function find(string $id): ?Adapter
    {
        foreach ($this->getArrayCopy() as $item) {
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