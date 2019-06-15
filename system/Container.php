<?php
namespace system;
class Container{
	// 向容器中绑定服务对象
	protected $binds = array();

	// 是一个简单的服务容器里面有bind,make两个方法
	protected $instances = array();

	// 单例的对象
	protected static $instance;

	/**
     * 获取单例对象
     * @return \system\Cache
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

	/**
	 * 注入容器
	 * @param  [type] $abstract [description]
	 * @param  [type] $concrete [description]
	 * @return [type]           [description]
	 */
	public function bind($abstract , $concrete){
		if($concrete instanceof \Closure){
			$this->binds[$abstract] = $concrete;
		}else{
			$this->instances[$abstract] = $concrete;
		}
	}

	/**
	 * 取对象
	 * @param  [type] $abstract [description]
	 * @param  array  $params   [description]
	 * @return [type]           [description]
	 */
	public function make($abstract , $params = array()){
		if(isset($this->instances[$abstract])){
			return $this->instances[$abstract];
		}
		array_unshift($params , $this);
		return call_user_func_array($this->binds[$abstract] , $params);
	}
}