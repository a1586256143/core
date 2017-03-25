<?php
/**
 * 引导
 * @author Colin <15070091894@163.com>
 */
//MyClass目录
define('MyClass' , str_replace('\\' , '/' , dirname(__FILE__)));
//根目录
define('ROOT_PATH' , substr(MyClass , 0 , -6));
//APP路径
define('APP_PATH' , substr(MyClass , 0 , -6));
//核心文件
define('Core' , MyClass . '/');
//第三方类库文件目录
define('Library' , APP_PATH . 'librarys');
//定义运行目录
define('RunTime' , APP_PATH . 'runtimes');
//公共文件目录
define('Common' , APP_PATH . 'globals');
//系统公共目录
define('CommonDIR' , Core . 'Common');
//定义版本信息
define('VERSION' , '3.0');
//引入MyClass核心文件
require_once Core . 'MyClass.php';
//执行run方法
system\MyClass::run();
?>