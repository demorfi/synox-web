<?php declare(strict_types=1);

namespace App\Components\Storage;

use App\Components\Helper;
use Digua\Components\Journal as JournalStorage;
use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};

class Journal extends JournalStorage
{
    /**
     * Status enabled journal.
     *
     * @var bool
     */
    protected bool $enabled = true;

    /**
     * @throws PathException
     * @throws StorageException
     */
    protected function __construct()
    {
        parent::__construct();
        $this->enabled = Helper::config('app')->get('journal');

        // Auto clean journal
        if (($offset = Helper::config('app')->get('clean-journal')) > 0 && $this->dataFile->size() > $offset) {
            $this->flush($offset);
        }
    }

    /**
     * @inheritdoc
     */
    public function push(string ...$message): bool
    {
        return $this->enabled && parent::push(...$message);
    }
}
