<?php

namespace Framework\Components\Pagination;

use Framework\Request;

class OfArray implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $array = [];

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $total = 1;

    /**
     * @var int
     */
    protected $current = 1;

    /**
     * @var Request
     */
    private $request;

    /**
     * PaginationArray constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Set elements for create pagination.
     *
     * @param array $array Elements
     * @param int $limit
     * @return self
     */
    public function setElements(array $array, $limit)
    {
        $page = $this->request->getQuery()->get('page');

        $this->array   = $array;
        $this->limit   = $limit;
        $this->total   = ceil(sizeof($array) / $limit);
        $this->current = (!$page ? 1 : $page);
        $this->offset  = ($this->current > 1 ? ($this->current - 1) * $this->limit : 0);

        return ($this);
    }

    /**
     * Get elements on current page.
     *
     * @return array
     */
    public function getElementsOnPage()
    {
        return (array_slice($this->array, $this->offset, $this->limit));
    }

    /**
     * Has next page.
     *
     * @return bool
     */
    public function hasNext()
    {
        return ($this->current < $this->total);
    }

    /**
     * Has prev page.
     *
     * @return bool
     */
    public function hasPrev()
    {
        return ($this->current > 1 && $this->current <= $this->total);
    }

    /**
     * Has pages.
     *
     * @return bool
     */
    public function hasPages()
    {
        return ($this->total > 1);
    }

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrent()
    {
        return ($this->current);
    }

    /**
     * Get total pages.
     *
     * @return int
     */
    public function getTotal()
    {
        return ($this->total);
    }

    /**
     * Get next page.
     *
     * @param string $url
     * @return string
     */
    public function getNextPage($url = null)
    {
        return ((!is_null($url) ? $url . '/page/' : '') . ($this->hasNext() ? $this->current + 1 : $this->current));
    }

    /**
     * Get prev page.
     *
     * @param string $url
     * @return string
     */
    public function getPrevPage($url = null)
    {
        return ((!is_null($url) ? $url . '/page/' : '') . ($this->hasPrev() ? $this->current - 1 : $this->current));
    }

    /**
     * Get navigation list.
     *
     * @param string $url
     * @return \Generator
     */
    public function getNavigation($url = null)
    {
        for ($i = 1; $i <= $this->total; $i++) {
            yield [
                'page'   => $i,
                'url'    => (!empty($url) ? $url . '/page/' : '') . $i,
                'active' => $i == $this->current
            ];
        }
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        return ([
            'hasPages' => $this->hasPages(),
            'hasNext'  => $this->hasNext(),
            'hasPrev'  => $this->hasPrev(),
            'total'    => $this->getTotal(),
            'current'  => $this->getCurrent(),
            'nextPage' => $this->getNextPage(''),
            'prevPage' => $this->getPrevPage('')
        ]);
    }
}