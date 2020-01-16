<?php
/**
 * 主体引导
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\IO\Build\Build;

class MyClass {
    /**
     * 运行方法
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public static function run() {
        //加载配置文件
        self::loadConfig();
        //注册autoload方法
        spl_autoload_register('system\\MyClass::autoload');
        //收集错误
        MyError::traceError();
        // Build
        $build = Build::getInstance();
        $build->exec();
        // 启动
        self::start();
    }

    /**
     * 自动加载
     *
     * @param string $ClassName 类名
     *
     * @author Colin <15070091894@163.com>
     */
    public static function autoload($ClassName) {
        if (preg_match("/\\\\/", $ClassName)) {
            //是否为命名空间加载
            $ClassName = preg_replace("/\\\\/", DIRECTORY_SEPARATOR, $ClassName);
            // 简单处理app命名空间
            list($dirname) = explode(DIRECTORY_SEPARATOR, $ClassName);
            $path      = APP_PATH . APP_NAME . DIRECTORY_SEPARATOR;
            $ClassName = $dirname != 'system' ? $path . $ClassName : NAME_SPACE . $ClassName;
            require_file($ClassName . Config('DEFAULT_CLASS_SUFFIX'));
        }
    }

    /**
     * 加载配置文件
     * @author Colin <15070091894@163.com>
     */
    public static function loadConfig() {
        //DEBUG
        if (!defined('Debug')) define('Debug', true);
        //载入函数库文件
        require_once CommonDIR . '/functions.php';
        $config = require_file(Core . '/Conf/config.php');
        // 解析.env文件
        if (is_file(Common . '/.env')) {
            $env = parse_ini_file(Common . '/.env');
            //合并config文件内容
            $config = array_replace_recursive($config, $env);
        }
        //加入配置文件
        Config($config);
        //解析session
        if (Config('SESSION_START')) {
            session_start();
        }
        //设置默认时间格式
        date_default_timezone_set(Config('TIMEZONE'));
    }

    /**
     * 执行一系列操作
     * @throws \SmartyException
     * @throws \system\MyError
     */
    public static function start() {
        //加载配置文件
        $requires = [
            Common . '/routes.php',
            Common . '/csrf.php',
            Common . '/template.php',
            Common . '/functions.php'
        ];
        //批量引入
        require_file($requires);
        //初始化视图工厂
        View::init(Config('TPL_MODEL'), Config('TPL_CONFIG'));
        //执行路由
        Route\Route::init();
    }
}
