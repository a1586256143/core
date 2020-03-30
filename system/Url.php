<?php
/**
 * URL处理
 * @author Colin <15070091894@163.com>
 */

namespace system;
class Url {
    public static $param = [];

    /**
     * 解析URL，得到url后面的参数
     *
     * @param string $url 被解析的地址，转换成路由，为空，则默认当前
     *
     * @return string
     */
    public static function parseUrl($url = null) {
        $pathinfo = PHP_SAPI !== 'cli' ? $_SERVER['REQUEST_URI'] : '';
        // 解析地址，得到path和query
        $parse    = parse_url($pathinfo);
        $pathinfo = rtrim($parse['path'], '/');
        if (isset($parse['query'])) {
            // 把query解析成数组
            parse_str($parse['query'], $query);
            foreach ($query as $key => $value) {
                // 处理参数，防SQL注入
                $_GET[ $key ] = trim($value);
            }
        }
        if ($pathinfo == '/') {
            if (!Config('ROUTE_STATUS')) {
                $pathinfo = sprintf('/%s/%s', Config('DEFAULT_CONTROLLER'), Config('DEFAULT_METHOD'));
            }
        }
        // 去除当前访问的文件名
        $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
        $pathinfo   = str_replace('/' . $scriptName, '', $pathinfo);
        if (strpos($pathinfo, '/') === 0) {
            return $pathinfo;
        }

        return '/' . $pathinfo;
    }

    /**
     * 获取当前url
     *
     * @param boolean $is_return_current_url 是否返回当前地址
     * @param boolean $is_return_array       是否返回数组
     *
     * @return array|string
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public static function getCurrentUrl($is_return_current_url = false, $is_return_array = false) {
        $current_url = self::getSiteUrl();
        $parse_url   = parse_url($current_url);
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
            $parse_path = [Config('DEFAULT_CONTROLLER'), Config('DEFAULT_METHOD')];
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

    /**
     * 获取请求的URL
     * @return mixed
     */
    public static function getFullUrl() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }
}
