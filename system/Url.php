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
     * @return string
     */
    public static function parseUrl() {
        $pathinfo = PHP_SAPI !== 'cli' ? $_SERVER['REQUEST_URI'] : '';
        // 解析地址，得到path和query
        $parse    = parse_url($pathinfo);
        $pathinfo = rtrim($parse['path'], '/');
        if (isset($parse['query'])) {
            // 把query解析成数组
            parse_str($parse['query'], $query);
            self::clearQuery($query);
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
     * 清理每一项数据
     *
     * @param $query
     */
    protected static function clearQuery($query) {
        foreach ($query as $key => $value) {
            // 处理参数，防SQL注入
            $value = is_array($value) ? self::clearItem($value) : trim($value);

            $_GET[ $key ] = $value;
        }
    }

    /**
     * 清理数据
     *
     * @param $data
     *
     * @return mixed|string
     */
    public static function clearData($data) {
        if (is_array($data)) {
            $data = self::clearItem($data);

            return $data;
        } else {
            return self::trimItem($data);
        }
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    protected static function clearItem($items) {
        foreach ($items as &$val) {
            $val = is_array($val) ? self::clearItem($val) : self::trimItem($val);
        }
        unset($val);

        return $items;
    }

    /**
     * 去除前后空格
     *
     * @param $item
     *
     * @return string
     */
    protected static function trimItem($item) {
        return trim($item);
    }

    /**
     * 获取请求的URL
     * @return mixed
     */
    public static function getFullUrl() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    /**
     * 获取请求的域名
     *
     * @param bool $local 本地
     *
     * @return string
     */
    public static function getFullHost($local = false) {
        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . ':' : '';
        $host   = $scheme . '//' . $_SERVER['HTTP_HOST'];
        if ($local) {
            return '';
        }

        return $host;
    }
}
