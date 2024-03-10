<?php declare(strict_types=1);

namespace App\Package\Search;

use Digua\Components\ArrayCollection;
use JsonSerializable;

class Profile implements JsonSerializable
{
    /**
     * @var ArrayCollection
     */
    protected ArrayCollection $packages;

    /**
     * @param array|ArrayCollection $list
     */
    public function __construct(array|ArrayCollection $list)
    {
        $this->packages = ArrayCollection::make();
        foreach ($list as $packageId => $filters) {
            $values = ArrayCollection::make();
            (new Filter((array)$filters))
                ->each(static function ($filter) use (&$values) {
                    $values = $values->merge([$filter->getFilterId() => [$filter->getName()]], true);
                });

            if (!$values->isEmpty()) {
                $this->packages = $this->packages->merge([$packageId => $values->toArray()], true);
            }
        }
    }

    /**
     * @param array|ArrayCollection $list
     * @return static
     */
    public static function create(array|ArrayCollection $list): static
    {
        return new static($list);
    }

    /**
     * @return ArrayCollection
     */
    public function collection(): ArrayCollection
    {
        return $this->packages;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->packages->isEmpty();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->packages->toArray();
    }
}