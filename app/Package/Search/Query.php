<?php declare(strict_types=1);

namespace App\Package\Search;

use App\Package\{Adapter, Collection};
use Digua\Components\ArrayCollection;

readonly class Query
{
    /**
     * @param string             $value
     * @param Collection|Adapter $packages
     * @param ?Filter            $filter
     * @param array              $params
     */
    public function __construct(
        public string $value,
        protected Collection|Adapter $packages,
        public ?Filter $filter = null,
        public array $params = []
    ) {
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->filter instanceof Filter;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->value) < 1) {
            return false;
        }

        if ($this->packages instanceof Collection && $this->packages->count() < 1) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->packages instanceof Collection ? $this->packages->count() : 1;
    }

    /**
     * @return ArrayCollection
     */
    public function split(): ArrayCollection
    {
        $queries = ArrayCollection::make();
        foreach ($this->getPackages() as $package) {
            // Personal filtering
            $filter    = $this->filter?->collection()->get($package->getId()) ?? $this->filter?->collection() ?? [];
            $queries[] = new static($this->value, $package, !empty($filter) ? new Filter((array)$filter) : null, $this->params);
        }
        return $queries;
    }

    /**
     * @return Collection
     */
    public function getPackages(): Collection
    {
        return $this->packages instanceof Collection ? $this->packages : Collection::make([$this->packages]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParam(string $name): bool
    {
        return isset($this->params[$name]);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return bool
     */
    public function equalParam(string $name, mixed $value): bool
    {
        return isset($this->params[$name]) && $this->params[$name] === $value;
    }

    /**
     * @return ArrayCollection
     */
    public function getParamsCollection(): ArrayCollection
    {
        return ArrayCollection::make($this->params);
    }
}