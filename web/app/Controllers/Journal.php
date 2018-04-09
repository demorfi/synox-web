<?php

namespace Controllers;

use Framework\Abstracts\Controller;
use Classes\Journal as StorageJournal;

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
        $journal = StorageJournal::getInstance()->getJournal();
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
