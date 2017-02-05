<?php

namespace Framework;

use Framework\Abstracts\Data;
use Framework\Traits\Output;

class Template extends Data
{
    use Output;

    /**
     * @var string
     */
    const PATH = APP_PATH . '/Views/';

    /**
     * Sections.
     *
     * @var array
     */
    private $sections = [];

    /**
     * Extends.
     *
     * @var array
     */
    private $extends = [];

    /**
     * Data template.
     *
     * @var array
     */
    private $data = [];

    /**
     * Template name.
     *
     * @var string
     */
    private $tpl = '';

    /**
     * Request.
     *
     * @var Request
     */
    private $request;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->startBuffer();
        $this->request = new Request();
    }

    /**
     * Render template.
     */
    public function __destruct()
    {
        $this->view($this->tpl);

        for ($i = 0; $i < sizeof($this->extends); $i++) {
            $this->view($this->extends[$i]);
        }
    }

    /**
     * Wrap template.
     *
     * @param string $name Template name
     * @return string
     */
    public function inject($name)
    {
        $this->view($name);

        $this->section($name);
        for ($i = 0; $i < sizeof($this->extends); $i++) {
            $this->view($this->extends[$i]);
        }
        $this->endSection($name);

        $this->extends = [];
        return ($this->block($name));
    }

    /**
     * Has active route.
     *
     * @param string $path Route path
     * @return bool
     */
    public function hasRoute($path)
    {
        return ((bool)preg_match('/^' . preg_quote($path, '/') . '/', $this->request->getQuery()->getRoute()));
    }

    /**
     * Add template for render.
     *
     * @param string $name Template name
     * @param array $variables Added template variables
     * @return Template
     */
    public function render($name, $variables = [])
    {
        $this->tpl   = $name;
        $this->array = $variables;
        return ($this);
    }

    /**
     * Include template.
     *
     * @param string $name Template name
     * @return void
     */
    public function view($name)
    {
        require(static::PATH . $name . PHP_EXT);
    }

    /**
     * Add template extend.
     *
     * @param string $name Template name
     * @return void
     */
    public function extend($name)
    {
        $this->extends[] = $name;
    }

    /**
     * Fetch section.
     *
     * @param string $name Section name
     * @return string|null
     */
    public function block($name)
    {
        return (isset($this->sections[$name]) ? $this->sections[$name] : null);
    }

    /**
     * Init section.
     *
     * @param string $name Section name
     * @return void
     */
    public function section($name)
    {
        $this->sections[$name] = $this->startBuffer();
    }

    /**
     * Close init section.
     *
     * @param string $name Section name
     * @return void
     */
    public function endSection($name)
    {
        if (isset($this->sections[$name]) && ($this->sections[$name] == true)) {
            $this->sections[$name] = $this->flushBuffer();
            $this->cleanBuffer();
        }
    }

    /**
     * Init short section for include template.
     *
     * @param string $name Section name
     * @param string $tpl Template name
     */
    public function shortSection($name, $tpl)
    {
        $this->section($name);
        $this->view($tpl);
        $this->endSection($name);
    }

    /**
     * Proxy.
     *
     * @param string $name Method name
     * @param array $args Method arguments
     * @return mixed
     */
    public function __call($name, $args)
    {
        return (empty($args) ? (isset($this->data[$name]) ? $this->data[$name] : []) : $this->data[$name] = $args[0]);
    }
}