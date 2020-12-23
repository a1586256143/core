<?php

namespace system;
use Closure;

class Facade {
    /**
     * @param string $classname
     * @param array $args
     * @return Closure|mixed|string
     */
    public static function getInstance($classname = '', $args = []) {
        if ($classname instanceof Closure) {
            return $classname;
        }

        return new $classname($args[0]);
    }

    /**
     * @return string
     */
    public static function getFacadeAccessor() {
        return '';
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args) {
        $instance = static::getInstance(static::getFacadeAccessor(), $args);

        return call_user_func_array([$instance, $method], $args);
    }
}
