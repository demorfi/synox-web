<?php declare(strict_types=1);

namespace App\Repositories;

use App\Components\Helper;
use App\Package\Adapter;
use App\Collections\Package as Collection;
use App\Interfaces\Package as PackageInterface;
use App\Exceptions\Package as PackageException;
use Digua\Components\DataFile;
use Digua\Traits\Singleton;
use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};

class Packages
{
    use Singleton;

    /**
     * @var Collection
     */
    private Collection $packages;

    /**
     * @throws PackageException
     * @throws PathException
     * @throws StorageException
     */
    private function __construct()
    {
        $this->packages = new Collection();
        $packagesNames  = array_unique(Helper::config('packages')->getAll());
        if (!empty($packagesNames)) {
            foreach ($packagesNames as $packageName) {
                $packageClass = 'App\Components\Packages\\' . $packageName;
                if (!is_subclass_of($packageClass, PackageInterface::class)) {
                    throw new PackageException($packageName . ' - is invalid package');
                }

                $settings = DataFile::create(strtolower($packageName));
                $this->packages->append(new Adapter(new $packageClass($settings), $settings));
            }
        }
    }

    /**
     * @return Collection
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }
}
