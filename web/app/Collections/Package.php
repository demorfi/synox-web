<?php declare(strict_types=1);

namespace App\Collections;

use App\Enums\PackageType;
use App\Package\PackageAdapter;
use ArrayIterator;

class Package extends ArrayIterator
{
    /**
     * @var ?PackageType
     */
    public ?PackageType $type = null;

    /**
     * @param ?PackageType $type
     * @return void
     */
    public function setType(?PackageType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ?PackageType
     */
    public function getType(): ?PackageType
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
            if ($item instanceof PackageAdapter && $filter($item)) {
                $instance->append($item);
                continue;
            }

            if ($item instanceof self) {
                foreach ($item->filter($filter) as $subItem) {
                    $instance->append($subItem);
                }
            }
        }

        $instance->setType($this->getType());
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
     * @param PackageType ...$types
     * @return self
     */
    public function getByType(PackageType ...$types): self
    {
        $instance = new self;
        foreach ($types as $type) {
            $subInstance = $this->filter(fn($item) => $item->getType() == $type);
            $subInstance->setType($type);
            $instance->append($subInstance);
        }

        return sizeof($types) > 1 ? $instance : $instance->offsetGet(0);
    }

    /**
     * @param string $id
     * @return ?PackageAdapter
     */
    public function find(string $id): ?PackageAdapter
    {
        foreach ($this->getArrayCopy() as $item) {
            if ($item instanceof PackageAdapter && $item->getId() == $id) {
                return $item;
            }

            if ($item instanceof self && ($subItem = $item->find($id)) instanceof PackageAdapter) {
                return $subItem;
            }
        }

        return null;
    }
}