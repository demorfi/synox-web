<?php declare(strict_types=1);

namespace App\Package;

use App\Components\{Helper, Settings};
use App\Package\Enums\Type;
use Digua\Exceptions\{Path as PathException, Storage as StorageException};
use Digua\Traits\Singleton;

class Repository
{
    use Singleton;

    /**
     * @var Collection
     */
    private Collection $packages;

    /**
     * @throws PathException
     * @throws StorageException
     */
    private function __construct()
    {
        $this->packages = new Collection;

        foreach (Helper::config('packages')->collection() as $type => $packages) {
            if (empty($packages) || is_null($type = Type::tryName($type))) {
                continue;
            }

            foreach ($packages as $package) {
                $class = 'App\Components\Packages\\' . $type->getName() . '\\' . $package;
                if (is_subclass_of($class, $type->getInterface())) {
                    $settings = Settings::create($type->getId() . '-' . strtolower($package));
                    $this->packages->append(new Adapter($type->makeRelay(new $class($settings), $settings), $settings));
                }
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