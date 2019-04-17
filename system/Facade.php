<?php
namespace system;
class Facade{
    public static function getInstance($classname,$args){

    	if($classname instanceof \Closure){
    		return $classname;
    	}
        return new $classname($args);
    }

    public static function getFacadeAccessor(){
        
    }

    public static function __callstatic($method,$args){
        $instance = static::getInstance(static::getFacadeAccessor() , $args);
        return call_user_func_array(array($instance , $method) , $arg);
    }
}