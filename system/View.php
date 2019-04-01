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
    public static function init($type, $config = array()) {
        self::$view = ObjFactory::CreateTemplates($type, $config);
    }

    /**
     * display方法
     * @author Colin <15070091894@163.com>
     */
    public static function display($filename) {
        return self::$view->display($filename);
    }

    /**
     * assign方法
     * @author Colin <15070091894@163.com>
     */
    public static function assign($name, $value) {
        return self::$view->assign($name, $value);
    }

    /**
     * 创建index.php 模板
     * @author Colin <15070091894@163.com>
     */
    public static function createIndex($default = 'Index') {
        $string = "<?php 
namespace controllers;
use system\Base;
class $default extends Base{
	public function index(){
		return 'Welcome to use MyClassPHP';
	}
}
";

        return $string;
    }

    /**
     * 创建config.php 模板
     * @author Colin <15070091894@163.com>
     */
    public static function createConfig() {
        $string = "<?php
return array(
	//配置名 => 配置值
    'DB_HOST' => 'localhost' ,  //数据库地址
    'DB_TYPE' => 'mysqli' ,     //数据库类型
    'DB_TABS' => '' ,           //数据表名
    'DB_USER' => 'root' ,       //数据库用户
    'DB_PASS' => '' ,           //数据库密码
    'DB_CODE' => 'utf8' ,       //数据库编码
    'DB_PORT' => '3306' ,         //数据库端口
    'DB_PREFIX' => '' ,         //数据库前缀
);
";

        return $string;
    }

    /**
     * 创建默认template.php模板
     * @author Colin <15070091894@163.com>
     */
    public static function createTemplate() {
        $string = "<?php
//此文件为模板中使用的__常量名__格式配置文件，配置格式为
//if(!defined('常量名')) define('常量名' , '常量值');
if(!defined('__URL__')) define('__URL__' , getCurrentUrl());
if(!defined('__PUBLIC__')) define('__PUBLIC__' , Config('PUBLIC_DIR'));
";

        return $string;
    }

    /**
     * 创建默认routes.php模板
     * @author Colin <15070091894@163.com>
     */
    public static function createRoute() {
        $string = "<?php
/**
 * 该页面为网站路由
 * 所有路由全部在Route::add，如果要新增继续往后追加即可
 * 对应的格式为
 * '访问路由' => '访问方法'
 */
use system\Route\Route;
Route::add(array(
    '/' => '\controllers\Index@index'
));
";

        return $string;
    }

    /**
     * 创建默认csrf.php模板
     * @author Colin <15070091894@163.com>
     */
    public static function createCSRF() {
        $string = "<?php
/**
 * csrf配置表
 * 在html中调用_token()会生成csrf表单和值
 * 在js中调用_token(true)会生成csrf值
 */
use system\Route\CSRF;
//设置允许不进行CSRF验证的路由
CSRF::setAllow(array(
    //'/loginAction' , //对'/loginAction'这个路由不验证CSRF
));
";

        return $string;
    }

    /**
     * 创建函数文件
     * @return [type] [description]
     */
    public static function createFunc() {
        $string = "<?php
";

        return $string;
    }
}

?>