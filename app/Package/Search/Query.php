<?php declare(strict_types=1);

namespace App\Package\Search;

use Digua\Components\ArrayCollection;

readonly class Query
{
    /**
     * @param string  $value
     * @param ?Filter $filter
     * @param array   $params
     */
    public function __construct(
        public string $value,
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