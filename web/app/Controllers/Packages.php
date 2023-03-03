<?php declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Packages as PackagesRepository;
use Digua\Controllers\Base as BaseController;
use Digua\Exceptions\Storage as StorageException;
use Digua\Request;

class Packages extends BaseController
{
    /**
     * @var PackagesRepository
     */
    protected PackagesRepository $packages;

    /**
     * @inheritdoc
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->packages = PackagesRepository::getInstance();
    }

    /**
     * Change package status.
     *
     * @return array
     * @throws StorageException
     */
    public function statusAction(): array
    {
        $pkg  = [];
        $data = $this->dataRequest()->post()->get('pkg');
        if (!empty($data) && is_array($data)) {
            foreach ($data as $id => $enabled) {
                $status  = ($enabled == 'true' || $enabled == 1);
                $package = $this->packages->getPackages()->find($id);
                if ($package) {
                    $pkg[$id] = $status;
                    $package->__set('enabled', $status);
                    $package->saveSettings();
                }
            }
        }

        return compact('pkg');
    }

    /**
     * Change package settings.
     *
     * @return array
     * @throws StorageException
     */
    public function settingsAction(): array
    {
        $id = $this->dataRequest()->post()->get('pkg');
        if (empty($id)) {
            return ['success' => false, 'error' => 'Incorrect request!'];
        }

        $package = $this->packages->getPackages()->find($id);
        if (!$package) {
            return ['success' => false, 'error' => 'Package not found!'];
        }

        $settings = $this->dataRequest()->post()->get('data');
        if (!empty($settings) && is_array($settings)) {
            foreach ($settings as $key => $value) {
                $package->__set($key, $value);
            }
            $package->saveSettings();
        }

        return ['success' => true, 'settings' => $package->getSettings()];
    }
}
