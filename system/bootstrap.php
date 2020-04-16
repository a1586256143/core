<?php
/**
 * 引导
 * @author Colin <15070091894@163.com>
 */

//根目录
define('ROOT_PATH', MyClass . DIRECTORY_SEPARATOR);
//APP路径
define('APP_PATH', ROOT_PATH);
//核心文件
define('Core', dirname(__FILE__) . DIRECTORY_SEPARATOR);
//system
define('NAME_SPACE', substr(Core, 0, -7));
//系统app名字
define('APP_NAME', '');
//是否CLI模式
define('PHP_CLI', PHP_SAPI === 'cli' ? true : false);
//系统app目录
define('APP_DIR', !PHP_CLI ? APP_PATH . APP_NAME . DIRECTORY_SEPARATOR : APP_PATH . DIRECTORY_SEPARATOR);
//第三方类库文件目录
define('Library', APP_DIR . 'librarys');
//定义运行目录
define('RunTime', APP_DIR . 'runtimes');
//公共文件目录
define('Common', APP_DIR . 'config');
//系统公共目录
define('CommonDIR', Core . 'Common');
//定义版本信息
define('VERSION', '3.2.3');
//引入MyClass核心文件
require Core . 'MyClass.php';
//执行run方法
system\MyClass::run();
