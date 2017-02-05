<?php

namespace Controllers;

use Classes\Packages as _Packages;
use Framework\Abstracts\Controller;
use Framework\Request;
use Framework\Response;

class Packages extends Controller
{
    /**
     * @var _Packages
     */
    protected $packages;

    /**
     * @inheritdoc
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->packages = _Packages::getInstance();
    }

    /**
     * Package status.
     *
     * @return mixed
     */
    public function statusAction()
    {
        if ($this->request->getData()->has('pkg')) {
            foreach ($this->request->getData()->get('pkg') as $id => $enabled) {
                $package = $this->packages->getPackages()->find($id);
                if ($package) {
                    $package->__set('enabled', ($enabled == 'true' || $enabled == 1));
                    $pkg[$id] = true;
                }
            }
        }

        return ($this->response->json(compact('pkg')));
    }

    /**
     * Change package settings.
     *
     * @return mixed
     */
    public function settingsAction()
    {
        if ($this->request->getData()->has('pkg')) {
            $package = $this->packages->getPackages()->find($this->request->getData()->get('pkg'));
            if ($package) {
                if ($this->request->getData()->has('data')) {
                    foreach ($this->request->getData()->get('data') as $key => $value) {
                        $package->__set($key, $value);
                    }
                }
                return ($this->response->json(['success' => true, 'settings' => $package->getSettings()]));
            }
        }

        return ($this->response->json(['success' => false, 'error' => 'Package not found!']));
    }
}
