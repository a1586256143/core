<?php
/**
 * 视图显示
 * @author Colin <15070091894@163.com>
 */

namespace system;
class View {
    //静态成员
    public static $view;

    /**
     * 初始化成员信息
     *
     * @param type 类型
     *
     * @author Colin <15070091894@163.com>
     */
    public static function init($type, $config = []) {
        self::$view = Factory::CreateTemplates($type, $config);
        self::register();
    }

    /**
     * 注册smarty解析
     * @return null
     */
    protected static function register() {
        self::$view->register_prefilter('smarty_preFilterConstants');
        self::$view->register_function('constant', 'functionHash');
    }
}