<?php

namespace system;
class Facade {
    public static function getInstance($classname, $args) {

        if ($classname instanceof \Closure) {
            return $classname;
        }

        return new $classname($args[0]);
    }

    public static function getFacadeAccessor() {
        return '';
    }

    public static function __callstatic($method, $args) {
        $instance = static::getInstance(static::getFacadeAccessor(), $args);

        return call_user_func_array([$instance, $method], $args);
    }
}