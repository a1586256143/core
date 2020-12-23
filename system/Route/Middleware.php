<?php
/**
 * 中间件
 * @author Colin <15070091894@163.com>
 */

namespace system\Route;

use system\Base;

abstract class Middleware extends Base {

    /**
     * 系统初始化
     * @author Colin <15070091894@163.com>
     */
    public function __construct() {
        parent::__construct();
        self::init();
    }

    /**
     * 初始化
     * @author Colin <15070091894@163.com>
     */
    public static function init() {

    }

    /**
     * 执行中间件
     *
     * @param Base  $controller 当前执行的控制器
     * @param Route $route      当前运行的路由类
     *
     * @author Colin <15070091894@163.com>
     * @return bool
     */
    abstract function execMiddleware(Base $controller, Route $route);
}
