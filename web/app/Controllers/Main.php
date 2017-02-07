<?php

namespace Controllers;

use Classes\Packages;
use Framework\Abstracts\Controller;

class Main extends Controller
{
    /**
     * Default action.
     *
     * @return mixed
     */
    public function defaultAction()
    {
        $title    = 'Home';
        $packages = Packages::getInstance()->getPackagesByType();
        return (tpl()->render('main', compact('title', 'packages')));
    }
}
