<?php

namespace system;
class Container extends Factory {
    // 向容器中绑定服务对象
    protected $binds = [];

    // 是一个简单的服务容器里面有bind,make两个方法
    protected $instances = [];
    
    /**
     * 注入容器
     *
     * @param  [type] $abstract [description]
     * @param  [type] $concrete [description]
     */
    public function bind($abstract, $concrete) {
        if ($concrete instanceof \Closure) {
            $this->binds[ $abstract ] = $concrete;
        } else {
            $this->instances[ $abstract ] = $concrete;
        }
    }

    /**
     * 取对象
     *
     * @param       $abstract
     * @param array $params
     *
     * @return mixed
     */
    public function make($abstract, $params = []) {
        if (isset($this->instances[ $abstract ])) {
            return $this->instances[ $abstract ];
        }
        array_unshift($params, $this);

        return call_user_func_array($this->binds[ $abstract ], $params);
    }
}
