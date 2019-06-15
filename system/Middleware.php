<?php
/**
 * 中间件
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\Route\Route;

abstract class Middleware extends Base {
    //不过滤
    protected static $notFilter;
    //过滤
    protected static $filter;

    /**
     * 系统初始化
     * @author Colin <15070091894@163.com>
     */
    public function __construct() {
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
     * @param \system\Base  $controller 当前执行的控制器
     * @param \system\Route $route      路由
     *
     * @author Colin <15070091894@163.com>
     */
    abstract public function execMiddleware(Base $controller, Route $route);
}