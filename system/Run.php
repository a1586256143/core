<?php
/**
 * 引导
 * @author Colin <15070091894@163.com>
 */

//根目录
define('ROOT_PATH', MyClass . '/');
//APP路径
define('APP_PATH', ROOT_PATH);
//核心文件
define('Core', dirname(__FILE__) . '/');
//system
define('NAME_SPACE', substr(Core, 0, -7));
//系统app名字
define('APP_NAME', 'app');
//系统app目录
define('APP_DIR', APP_PATH . APP_NAME . '/');
//第三方类库文件目录
define('Library', APP_DIR . 'librarys');
//定义运行目录
define('RunTime', APP_DIR . 'runtimes');
//公共文件目录
define('Common', APP_DIR . 'globals');
//系统公共目录
define('CommonDIR', Core . 'Common');
//定义版本信息
define('VERSION', '3.0');
//引入MyClass核心文件
require_once Core . 'MyClass.php';
//执行run方法
system\MyClass::run();