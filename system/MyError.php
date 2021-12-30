<?php
/**
 * 错误处理
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\IO\File\Log;
use Exception;

class MyError extends Exception {
    protected static $info;

    /**
     * 构造方法
     *
     * @param string $message 错误信息
     *
     * @author Colin <15070091894@163.com>
     */
    public function __construct($message) {
        parent::__construct();
        $this->message = $message;
        $this->file    = Debug ? $this->file : '';
        $this->line    = Debug ? $this->line : '';

    }

    /**
     * 错误处理
     *
     * @param string $errno   错误等级
     * @param string $errstr  错误信息
     * @param string $errfile 错误文件
     * @param string $errline 错误行数
     * @param string $detail  错误流程详情
     * @param bool   $log     记录日志
     *
     * @author Colin <15070091894@163.com>
     */
    public static function customError($errno, $errstr, $errfile, $errline, $detail, $log = false) {
        if ($errno == E_NOTICE || $errno == E_WARNING) {
            return;
        }
        if ((E_USER_WARNING !== $errno && !(error_reporting() & $errno)) || $log) {
            Log::error($errfile . ' ' . $errstr . ' line:' . $errline);
        }
        if (Debug) {
            self::info_initialize($errno, $errstr, $errfile, $errline, $detail);
        } else {
            self::info_initialize(0, Config('ERROR_MESSAGE'), '', '', null);
        }
        Log::generator();
        exit(self::$info);
    }

    /**
     * 错误处理
     * @author Colin <15070091894@163.com>
     */
    public static function shutdownFunction() {
        $e = error_get_last();
        if ($e) {
            self::customError($e['type'], $e['message'], $e['file'], $e['line'], null, true);
        }
    }

    /**
     * 收集错误
     * @author Colin <15070091894@163.com>
     */
    public static function traceError() {
        // 解决有时会出现错误的问题
        self::set_error_show();
        Log::addRecord(Url::getFullUrl(), true);
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_USER_WARNING ^ E_USER_NOTICE ^ E_USER_DEPRECATED ^ E_USER_ERROR);
        //设置错误处理
        set_error_handler('system\\MyError::customError');
        //设置错误处理
        register_shutdown_function('system\\MyError::shutdownFunction');
    }

    /**
     * 设置错误显示
     * @author Colin <15070091894@163.com>
     */
    protected static function set_error_show() {
        ini_set('display_errors', 'Off');
    }

    /**
     * info初始化
     *
     * @param string $code    错误等级
     * @param string $message 错误信息
     * @param string $file    错误文件
     * @param string $line    错误行数
     * @param string $detail  错误流程详情
     *
     * @author Colin <15070091894@163.com>
     */
    protected static function info_initialize($code, $message, $file, $line, $detail) {
        header('Content-type:text/html;charset="utf-8"');
        header("HTTP/1.1 404 Not Found");
        $debug      = Debug;
        $style      = <<<EOF
<style>
    div{width:100%;height:100%;margin:0 auto;font-family:'微软雅黑'}
    ul {list-style:none;width:100%;height:100%;padding:0px;margin:0px;}
    li{min-height:40px;line-height:40px;font-size:16px;color:#333;word-break: break-all;}
    li > pre{width:100%;line-height:25px;overflow-x:auto;font-size:13px;margin:0px;}
</style>
EOF;
        self::$info = '<!DOCTYPE html><html lang=""><head><meta charset="utf-8"></head><body><div><ul>';
        $debug && self::$info .= "<li><pre>" . $message . "</pre></li>";
        !$debug && self::$info .= "<li><pre>" . $message . "</pre></li>";
        $line = $line ? ":" . $line : '';
        $file && self::$info .= "<li>错误文件：" . $file . $line . "</li>";
        self::$info .= "<li>";
        if ($debug) {
            $string = array_filter(explode("#", $detail));
            if (is_array($string)) {
                foreach ($string as $key => $value) {
                    self::$info .= '#' . $value . '<br>';
                }
            }
        }
        self::$info .= "</li></ul></div></body></html>";
        self::$info .= $style;
    }
}
