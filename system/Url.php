<?php
/**
 * URL处理
 * @author Colin <15070091894@163.com>
 */

namespace system;
class Url {
    public static $param = array();

    /**
     * 解析URL，得到url后面的参数
     *
     * @param string $url 被解析的地址，转换成路由，为空，则默认当前
     *
     * @return [type] [description]
     */
    public static function parseUrl($url = null) {
        $pathinfo = $_SERVER['PATH_INFO'];
        if (!$pathinfo) {
            $pathinfo = '/';
        }

        return $pathinfo;
    }

    /**
     * 获取当前url
     *
     * @param boolean $is_return_current_url 是否返回当前地址
     * @param boolean $is_return_array       是否返回数组
     *
     * @return array
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public static function getCurrentUrl($is_return_current_url = false, $is_return_array = false) {
        $current_url = self::getSiteUrl();
        $parse_url   = parse_url($current_url);
        $path_array  = explode('/', ROOT_PATH);
        $patten      = '/\./';
        //匹配是否是index.php
        if (!empty($parse_path)) {
            if (preg_match($patten, $parse_path[0], $match)) {
                //确认文件是否存在
                if (!file_exists(ROOT_PATH . $parse_path[0])) {
                    E('无效的入口文件' . $parse_path[0]);
                }
                unset($parse_path[0]);
            }
            $parse_path = array_merge($parse_path);
        }
        if (empty($parse_path)) {
            $parse_path = array(Config('DEFAULT_MODULE'), Config('DEFAULT_CONTROLLER'), Config('DEFAULT_METHOD'));
        }
        self::$param = $parse_path;
        if ($is_return_current_url) return $current_url;
        if ($is_return_array) return $parse_url;

        return $parse_path;
    }

    /**
     * 获取域名
     *
     * @param boolean $isIndex 是否返回脚本名称
     *
     * @return string
     * @author Colin <15070091894@163.com>
     */
    public static function getSiteUrl($isIndex = false) {
        $hostName = $_SERVER['HTTP_HOST'];
        $params   = explode('/', $_SERVER['SCRIPT_NAME']);
        array_pop($params);
        $params = implode('/', $params);
        $scheme = $_SERVER['REQUEST_SCHEME'] . '://';
        if ($isIndex) {
            return $scheme . $hostName . $_SERVER['SCRIPT_NAME'];
        }

        return $scheme . $hostName . $params;
    }
}