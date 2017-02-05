<?php

namespace Classes;

use Classes\Interfaces\Package as _Package;
use Classes\Packages\Package;
use Classes\Packages\PackageList;
use Framework\Storage;
use Framework\Traits\Singleton;

class Packages
{
    use Singleton;

    /**
     * @var PackageList
     */
    private $packages;

    /**
     * @throws \Exception
     */
    protected function __init()
    {
        $this->packages = new PackageList();
        foreach (config('packages')->getAll() as $type => $packages) {
            $interfaceType = '\Classes\Interfaces\\' . rtrim($type, 's');
            $pathPrefix    = '\Packages\\' . ucfirst($type) . '\\';

            foreach ($packages as $package) {
                $path = $pathPrefix . $package . '\\' . $package;

                try {
                    if (!is_subclass_of($path, _Package::class) || !is_subclass_of($path, $interfaceType)) {
                        throw new \Exception($package . ' - is invalid package');
                    }

                    $settings = Storage::load(ucfirst(rtrim($type, 's') . '_' . $package));
                    $this->packages->append(new Package($type, new $path($settings), $settings));
                } catch (\Exception $e) {
                    if ($e->getMessage()) {
                        throw new \Exception($e->getMessage());
                    }

                    throw new \Exception($package . ' - package not found');
                }
            }
        }
    }

    /**
     * Get packages.
     *
     * @return PackageList
     */
    public function getPackages()
    {
        return ($this->packages);
    }

    /**
     * Get packages by type.
     *
     * @return PackageList
     */
    public function getPackagesByType()
    {
        return ($this->packages->getByTypes(config('packages')->getKeys()));
    }
}