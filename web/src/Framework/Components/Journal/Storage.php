<?php

namespace Framework\Components\Journal;

use Framework\Storage as _Storage;
use Framework\Traits\Singleton;

class Storage
{
    use Singleton;

    /**
     * Sort ASC.
     *
     * @var int
     */
    const SORT_ASC = 1;

    /**
     * Sort DESC.
     *
     * @var int
     */
    const SORT_DESC = 2;

    /**
     * Storage instance.
     *
     * @var _Storage
     */
    protected $journal;

    /**
     * Initialize.
     *
     * @return void
     * @throws \Exception
     */
    protected function __init()
    {
        $this->journal = _Storage::load('journal');
    }

    /**
     * Add message to journal.
     *
     * @param string $message
     * @return void
     */
    public function push($message)
    {
        $time = time();
        $this->journal->__set($this->journal->size() + 1, compact('message', 'time'));
    }

    /**
     * Flush journal.
     *
     * @return void
     */
    public function flush()
    {
        $this->journal->flush();
    }

    /**
     * Get journal.
     *
     * @param int $limit
     * @param int $sort
     * @return array
     */
    protected function getAll($limit = 0, $sort = self::SORT_DESC)
    {
        $journal = $this->journal->getAll();

        if ($sort == self::SORT_DESC) {
            $journal = array_reverse($journal);
        }

        if ($limit) {
            $journal = array_slice($journal, 0, $limit);
        }

        return ($journal);
    }

    /**
     * Get journal.
     *
     * @param int $limit
     * @param int $sort
     * @return \Generator
     */
    public function getJournal($limit = 0, $sort = self::SORT_DESC)
    {
        $journal = $this->getAll($limit, $sort);
        foreach ($journal as $item) {
            $item['date'] = date('Y-m-d H:m:s', $item['time']);
            yield $item;
        }
    }
}
