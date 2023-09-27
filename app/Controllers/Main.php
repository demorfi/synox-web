<?php declare(strict_types=1);

namespace App\Controllers;

use Digua\Controllers\Base as BaseController;
use Digua\Exceptions\Path as PathException;
use Digua\Template;

class Main extends BaseController
{
    /**
     * @return Template
     * @throws PathException
     */
    public function defaultAction(): Template
    {
        return $this->render('main');
    }
}
