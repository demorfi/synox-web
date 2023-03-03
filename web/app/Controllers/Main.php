<?php declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Packages as PackagesRepository;
use Digua\Controllers\Base as BaseController;
use Digua\Exceptions\Path as PathException;
use Digua\Template;

class Main extends BaseController
{
    /**
     * Default action.
     *
     * @return Template
     * @throws PathException
     */
    public function defaultAction(): Template
    {
        $title    = 'Home';
        $packages = PackagesRepository::getInstance()->getPackagesByType();
        return $this->render('main', compact('title', 'packages'));
    }
}
