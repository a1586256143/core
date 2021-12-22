<?php
/**
 * 基础类，处理视图数据
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\Route\AllowHeader;

class Base {
    //是否get
    protected static $is_get;
    //是否post
    protected static $is_post;
    //存储session
    protected static $session;
    //存储缓存
    protected static $cache;
    //存储get字段
    protected static $get;
    //存储post字段
    protected static $post;
    protected $app;
    /**
     * @var View
     */
    protected static $view;

    /**
     * 初始化函数
     * @author Colin <15070091894@163.com>
     */
    public function __construct() {
        self::init();
        $this->app = Factory::Container();
    }

    /**
     * 初始化
     * @throws
     */
    protected static function init() {
		self::$view    = View::getInstance();
		self::$is_get  = GET;
		self::$is_post = POST;
		self::$session = session();
		self::$get     = values('get.');
		self::$post    = values('post.');
		unset(self::$post['_token']);
    }

    /**
     * 重定向
     *
     * @param string url  跳转地址
     * @param string info  跳转时提示的信息
     * @param int time  跳转时间
     *
     * @author Colin <15070091894@163.com>
     *
     */
    protected static function redirect($url, $info = '', $time = 3) {
        if (!empty($info)) {
            echo "<meta http-equiv='refresh' content='$time; url=$url'/>";
            exit($info);
        }
        self::location($url);
    }

    /**
     * header跳转
     *
     * @param string url  跳转地址
     *
     * @author Colin <15070091894@163.com>
     *
     */
    protected static function location($url) {
        header("Location:$url");
    }

    /**
     * 返回json数据
     *
     * @param string|array $message 输出信息
     * @param string url  跳转地址
     * @param int status  信息状态
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    protected static function ajaxReturn($message, $url = null, $status = 0) {
        $return['info']   = $message;
        $return['url']    = $url;
        $return['status'] = $status;

        return ajaxReturn($return);
    }

    /**
     * 显示视图
     *
     * @param string $filename 文件名
     * @param array  $data     参数
     * @throws MyError
     */
    protected static function view($filename = null, $data = []) {
        self::$view->render($filename, $data, get_called_class());
    }

    /**
     * 注入变量
     *
     * @param string       $name  变量名
     * @param string|array $value 变量值
     */
    protected static function assign($name, $value = null) {
        self::$view->extractVars($name, $value);
    }

    /**
     * 读取session
     *
     * @param $name
     *
     * @return mixed
     */
    protected static function readSession($name = null) {
        $data = self::$session[ $name ];
        $json = json_decode($data, true);
        if (!$json) {
            return $data;
        }

        return $json;
    }

    /**
     * 设置session
     *
     * @param $data
     * @param $name
     *
     * @return bool
     * @throws MyError
     */
    protected static function setSession($data , $name = null) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        session($name, $data);

        return true;
    }

    /**
     * 删除session
     *
     * @param $name
     *
     * @return bool|null
     * @throws MyError
     */
    protected static function removeSession($name = '') {
        return session($name, null);
    }
}
