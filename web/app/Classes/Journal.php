<?php

namespace Classes;

use Framework\Components\Journal\Storage as StorageJournal;

class Journal extends StorageJournal
{
    /**
     * Status enabled journal.
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @inheritdoc
     */
    protected function __init()
    {
        $this->enabled = config('app')->get('journal');
        parent::__init();

        // Auto clean journal
        if (($limit = config('app')->get('clean-journal')) > 0) {
            if ($this->journal->size() > $limit) {
                $this->journal->overwrite(array_reverse($this->getAll($limit)));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function push($message)
    {
        if ($this->enabled) {
            parent::push($message);
        }
    }
}
