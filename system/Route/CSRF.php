<?php
/**
 * CSRF攻击处理
 * @author Colin <15070091894@163.com>
 */

namespace system\Route;

use system\MyError;
use system\Url;

class CSRF {
    //不过滤
    protected static $notFilter = [];

    /**
     * 设置过滤
     *
     * @param array $items 设置不验证CSRF的路由一维数组
     *
     * @author Colin <15070091894@163.com>
     */
    public static function setAllow($items = []) {
        foreach ($items as $key => $value) {
            self::$notFilter[] = $value;
        }
    }

    /**
     * 执行CSRF
     * @author Colin <15070091894@163.com>
     * @throws MyError
     */
    public static function execCSRF() {
        if (POST && Config('CSRF') == 1) {
            $url = Url::parseUrl();
            if (in_array($url, self::$notFilter)) {
                return;
            }
            if (!checkSecurity(values('post._token'))) {
                E('访问的链接丢失了...');
            }
        }
    }
}
