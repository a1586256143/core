<?php

namespace system\Route;

use system\Base;

class AddonsRoute extends Base {
    /**
     * 运行方法
     */
    public final function index() {
        list($className, $method) = $this->getClassName();
        $controller = new $className();
        try {
            Route::reflection($controller, $method);
        } catch (MyError $e) {
            return error($e->getMessage());
        }
    }

    /**
     * 获取将要执行的模块名和模块方法
     * @return array
     */
    protected final function getClassName() {
        $m = values('get.m');
        $a = values('get.a');
        unset($_GET['m'], $_GET['a']);
        $m          = str_replace('-', '/', $m);
        $default    = config('DEFAULT_CONTROLLER_LAYER');
        $method     = $a ? $a : config('DEFAULT_METHOD');
        $controller = config('DEFAULT_CONTROLLER');
        $mArray     = explode('/', $m);
        if (count($mArray) > 1) {
            $controller = array_pop($mArray);
        }
        $addon     = config('ADDON_PATH');
        $className = $addon . '\\' . implode('\\', $mArray) . '\\' . $default . '\\' . $controller;
        $path      = MyClass . DIRECTORY_SEPARATOR . str_replace('\\', '/', $className) . '.php';
        if (!is_file($path)) {
            exit(error('404 not found'));
        }

        return [$className, $method];
    }
}
