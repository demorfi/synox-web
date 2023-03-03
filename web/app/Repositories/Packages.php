<?php declare(strict_types=1);

namespace App\Repositories;

use App\Collections\Package as PackageCollection;
use App\Enums\PackageType;
use App\Package\PackageAdapter;
use App\Components\Helper;
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
     * @var PackageCollection
     */
    private PackageCollection $packages;

    /**
     * @throws PackageException
     * @throws PathException
     * @throws StorageException
     */
    private function __construct()
    {
        $this->packages = new PackageCollection();
        foreach (PackageType::cases() as $packageType) {
            $packagesNames = Helper::config('packages')->get($packageType->name);
            if (!empty($packagesNames)) {
                $interface = $packageType->getInterface();
                foreach ($packagesNames as $packageName) {
                    $packageClass = 'App\Components\Packages\\' . $packageType->name . '\\' . $packageName;

                    if (!is_subclass_of($packageClass, $interface)) {
                        throw new PackageException($packageName . ' - is invalid package');
                    }

                    $settings = DataFile::create(strtolower($packageType->name . '_' . $packageName));
                    $this->packages->append(new PackageAdapter($packageType, new $packageClass($settings), $settings));
                }
            }
        }
    }

    /**
     * @return PackageCollection
     */
    public function getPackages(): PackageCollection
    {
        return $this->packages;
    }

    /**
     * @return PackageCollection
     */
    public function getPackagesByType(): PackageCollection
    {
        return $this->packages->getByType(...PackageType::cases());
    }
}
