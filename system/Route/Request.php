<?php
namespace system\Route;
class Request{
    /**
     * 当前请求的方式
     * @return mixed
     */
    public function method(){
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 读取get参数
     * @param string $name
     * @param string $default
     * @return array|string|null
     */
    public function get($name = '' , $default = ''){
        return values('get.' . $name , $default);
    }

    /**
     * 读取所有get参数
     * @return array|string|null
     */
    public function gets(){
        return values('get.');
    }

    /**
     * 读取post参数
     * @param string $name
     * @param string $default
     * @return array|string|null
     */
    public function post($name = '' , $default = ''){
        return values('post.' . $name , $default);
    }

    /**
     * 读取所有post参数
     * @return array|string|null
     */
    public function posts(){
        return values('post.');
    }

    /**
     * 是否为post请求
     * @return bool
     */
    public function isPost(){
        return POST;
    }

    /**
     * 是否为get请求
     * @return bool
     */
    public function isGet(){
        return GET;
    }

    /**
     * 读取所有session
     * @return array|mixed|string|null
     * @throws \system\MyError
     */
    public function sessions(){
        return session();
    }

    /**
     * 设置单个session
     * @param $name
     * @throws \system\MyError
     * @return mixed
     */
    public function session($name){
        return session($name);
    }

}