<?php declare(strict_types=1);

namespace App\Controllers;

use App\Package\Repository;
use Digua\Controllers\Resource;
use Digua\Request;

abstract class Base extends Resource
{
    /**
     * @var Repository
     */
    protected Repository $repository;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->init();
    }

    /**
     * @return void
     */
    protected function init(): void
    {
        $this->repository = Repository::getInstance();
    }
}