<?php declare(strict_types=1);

namespace App\Package;

use App\Components\{Directory, File, Storage\Journal};
use App\Package\Exceptions\Package as PackageException;
use Digua\Traits\{Singleton, DiskPath};
use Digua\Exceptions\{Path as PathException, Storage as StorageException};

class Repository
{
    use Singleton, DiskPath;

    /**
     * @var array|string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/app/Components/Packages'
    ];

    /**
     * @var Collection
     */
    private readonly Collection $packages;

    /**
     * @throws PathException
     */
    private function __construct()
    {
        self::throwIsBrokenDiskPath();
        $this->packages = new Collection;
        $this->load();
    }

    /**
     * @return void
     * @throws PathException
     */
    private function load(): void
    {
        (new Directory(self::getDiskPath()))->each(function (File $fileInfo) {
            try {
                $this->addPackage(new State(new Source($fileInfo)));
            } catch (PackageException|StorageException $e) {
                Journal::staticPush($e->getMessage());
            }
        });
    }

    /**
     * @param State $package
     * @return bool
     * @throws PackageException
     */
    public function addPackage(State $package): bool
    {
        if (($adapter = $package->getAdapter()) !== null && $adapter->isActive()) {
            $this->packages->append($adapter);
            return true;
        }
        return false;
    }

    /**
     * @return Collection
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    /**
     * @return void
     */
    public function wakeup(): void
    {
        $this->packages
            ->getByEnabled()
            ->getByAvailable()
            ->each(static function (Adapter $package) {
                $package->wakeup();
            });
    }
}