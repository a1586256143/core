<?php
/**
 * CSRF攻击处理
 * @author Colin <15070091894@163.com>
 */

namespace system\Route;

use system\Url;

class CSRF {
    //不过滤
    protected static $notFilter = array();

    /**
     * 设置过滤
     *
     * @param array $items 设置不验证CSRF的路由一维数组
     *
     * @author Colin <15070091894@163.com>
     */
    public static function setAllow($items = array()) {
        foreach ($items as $key => $value) {
            self::$notFilter[] = $value;
        }
    }

    /**
     * 执行csrf
     * @author Colin <15070091894@163.com>
     */
    public static function execCSRF() {
        if (POST) {
            $url = Url::parseUrl();
            if (in_array($url, self::$notFilter)) {
                return;
            }
            if (!checkSecurity(values('post', '_token'))) {
                E('访问的链接丢失了...');
            }
        }
    }
}