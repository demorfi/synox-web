<?php

namespace Controllers;

use Framework\Abstracts\Controller;
use Framework\Components\Journal\Storage as StorageJournal;

class Journal extends Controller
{
    /**
     * Default action.
     *
     * @return mixed
     */
    public function defaultAction()
    {
        $title   = 'Journal';
        $journal = StorageJournal::getInstance()->getJournal(100);
        return (tpl()->render('journal', compact('title', 'journal')));
    }

    /**
     * Flush action.
     *
     * @return mixed
     */
    public function flushAction()
    {
        StorageJournal::getInstance()->flush();
        return ($this->response->location('/journal'));
    }
}
