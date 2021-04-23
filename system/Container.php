<?php

namespace system;
use Closure;

class Container extends Factory {
    // 向容器中绑定服务对象
    protected static $binds = [];

    // 是一个简单的服务容器里面有bind,make两个方法
    protected static $instances = [];
    
    /**
     * 注入容器
     *
     * @param string $alias 别名
     * @param mixed $class  对象或闭包
     * @throws MyError
     */
    public function bind($alias, $class) {
        if ($class instanceof Closure) {
            self::$binds[$alias] = $class;
        } else {
            if (is_object($class)){
                self::$instances[$alias] = $class;
            }else if (is_string($class)){
                self::$instances[$alias] = new $class;
            }else{
                throw new MyError('invalid class ' . $class);
            }
        }
    }

    /**
     * 取对象
     *
     * @param string $alias 别名
     * @param array $params 参数
     *
     * @return mixed
     */
    public function make($alias, $params = []) {
        if (isset(self::$instances[ $alias ])) {
            return self::$instances[ $alias ];
        }
        array_unshift($params, $this);

        return call_user_func_array(self::$binds[ $alias ], $params);
    }

    /**
     * 参数自动绑定
     * @param \ReflectionMethod $methodReflect
     * @param array $params
     * @return array
     * @throws MyError|\ReflectionException
     */
    public static function build(\ReflectionMethod $methodReflect , $params = []){
        $args = [];
        foreach ($methodReflect->getParameters() as $val){
            $name = $val->getClass() ? $val->getClass()->name : '';
            if ($name){
                $args[] = new $name;
            }else{
                if ($params[$val->getName()]){
                    $args[] = $params[$val->getName()];
                }else{
                    if (!$val->isDefaultValueAvailable()){
						throw new MyError('required params "' . $val->getName() . '"');
                    }
                }
            }
        }
        return $args;
    }
}
