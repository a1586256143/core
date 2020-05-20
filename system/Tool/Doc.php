<?php

namespace system\Tool;
class Doc {
    // 注释的信息
    protected static $data = [];

    /**
     * 获取注释的信息
     *
     * @param $data
     *
     * @return array
     */
    public static function getDocInfo($data) {
        preg_match_all('/@([a-zA-Z0-9]+)\s+([\w\\\\\x{4e00}-\x{9fa5}]+)?/u', $data, $match);
        if (!$match) {
            return [];
        }
        $key  = $match[1];
        $val  = $match[2];
        $data = [];
        foreach ($key as $k => $v) {
            $data[ $v ] = $val[ $k ];
        }
        self::$data = $data;

        return self::$data;
    }

    /**
     * 获取指定的一个注释
     *
     * @param string $name
     *
     * @return bool|mixed
     */
    public static function getDoc($name = '') {
        if (isset(self::$data)) {
            return self::$data[ $name ];
        }

        return false;
    }
}