<?php
/**
 * 视图显示
 * @author Colin <15070091894@163.com>
 */

namespace system;
class View {
    /**
     * @var $view \SmartyBC
     */
    public static $view;

    /**
     * 初始化成员信息
     *
     * @param string type 类型
     * @param array $config 视图的配置
     *
     * @author Colin <15070091894@163.com>
     * @throws \SmartyException
     */
    public static function init($type, $config = []) {
        self::$view = Factory::CreateTemplates($type, $config);
        self::register();
    }

    /**
     * 注册smarty解析
     * @return null
     * @throws \SmartyException
     */
    protected static function register() {
        self::$view->register_prefilter('smarty_preFilterConstants');
        self::$view->register_function('constant', 'functionHash');
    }
}
