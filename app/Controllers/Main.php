<?php declare(strict_types=1);

namespace App\Controllers;

use Digua\Exceptions\Path as PathException;
use Digua\Template;

class Main extends Base
{
    /**
     * @return Template
     * @throws PathException
     */
    public function getDefaultAction(): Template
    {
        return $this->render('main');
    }
}