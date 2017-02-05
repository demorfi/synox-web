<?php

namespace Framework\Traits;

trait Singleton
{
    /**
     * Instance.
     *
     * @var array
     */
    protected static $instance = [];

    /**
     * @inheritdoc
     */
    private function __construct()
    {
        return;
    }

    /**
     * @inheritdoc
     */
    private function __clone()
    {
        return;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception('object unserialize forbidden');
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function __sleep()
    {
        throw new \Exception('object serialize forbidden');
    }

    /**
     * Fake constructor.
     *
     * @return void
     */
    protected function __init()
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public static function __callStatic($method, $args)
    {
        return (call_user_func_array([self::$instance[get_called_class()], $method], $args));
    }

    /**
     * Get instance.
     *
     * @param string $name
     * @return static
     */
    public static function getInstance($name = null)
    {
        if (isset(self::$instance[$name])) {
            return (self::$instance[$name]);
        }

        $called = get_called_class();
        if (!isset(self::$instance[$called])) {
            self::$instance[$called] = new static();
            self::$instance[$called]->__init();
        }
        return (self::$instance[$called]);
    }

    /**
     * New instance.
     *
     * @return static
     */
    public static function newInstance()
    {
        $called = get_called_class();
        if (!isset(self::$instance[$called])) {
            self::$instance[$called] = new static();
            self::$instance[$called]->__init();
        }
        return (self::$instance[$called]);
    }
}