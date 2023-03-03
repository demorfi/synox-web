<?php declare(strict_types=1);

namespace App\Controllers;

use App\Components\Storage\Journal as JournalStorage;
use Digua\{Template, Response};
use Digua\Controllers\Base as BaseController;
use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};

class Journal extends BaseController
{
    /**
     * Default action.
     *
     * @return Template
     * @throws PathException
     */
    public function defaultAction(): Template
    {
        $title   = 'Journal';
        $journal = JournalStorage::getInstance()->getJournal();
        return $this->render('journal', compact('title', 'journal'));
    }

    /**
     * Flush action.
     *
     * @return Response
     * @throws StorageException
     */
    public function flushAction(): Response
    {
        JournalStorage::getInstance()->flush();
        return (new Response)->redirectTo('/journal');
    }
}
