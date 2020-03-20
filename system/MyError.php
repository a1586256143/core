<?php
/**
 * 错误处理
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\IO\File\Log;

class MyError extends \Exception {
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
        $this->file    = Debug ? $this->file : '未知';
        $this->line    = Debug ? $this->line : '未知';
    }

    /**
     * 错误处理
     *
     * @param string $errno   错误等级
     * @param string $errstr  错误信息
     * @param string $errfile 错误文件
     * @param string $errline 错误行数
     * @param string $detail  错误流程详情
     *
     * @author Colin <15070091894@163.com>
     */
    public static function customError($errno, $errstr, $errfile, $errline, $detail) {
        if (E_USER_WARNING !== $errno && !(error_reporting() & $errno)) {
            Log::error($errfile . ' ' . $errstr . ' line:' . $errline);

            return;
        }
        if (Debug) {
            self::info_initialize($errno, $errstr, $errfile, $errline, $detail);
        } else {
            self::info_initialize(7, Config('ERROR_MESSAGE'), '未知', '未知', null);
        }
        exit(self::$info);
    }

    /**
     * 错误处理
     * @author Colin <15070091894@163.com>
     */
    public static function shutdown_function() {
        $e = error_get_last();
        self::customError($e['type'], $e['message'], $e['file'], $e['line'], null);
    }

    /**
     * 收集错误
     * @author Colin <15070091894@163.com>
     */
    public static function traceError() {
        // 解决有时会出现错误的问题
        self::set_error_show();
        Log::addRecord(Url::getFullUrl(), true);
        error_reporting(E_PARSE | E_RECOVERABLE_ERROR | E_ERROR);
        //设置错误处理
        set_error_handler('system\\MyError::customError');
        //设置错误处理
        register_shutdown_function('system\\MyError::shutdown_function');
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
        self::$info = "<div style='width:85%;height:100%;margin:0 auto;font-family:微软雅黑'>";
        self::$info .= "<ul style='list-style:none;width:100%;height:100%;'>";
        self::$info .= "<li style='height:40px;line-height:40px;font-size:20px;color:#333;word-break: break-all;'>错误级别：" . $code . "</li>";
        self::$info .= "<li style='line-height:40px;font-size:20px;color:#333;word-break: break-all;'>错误信息：<pre style='width:100%;overflow-x:auto;font-size:13px'>" . $message . "</pre></li>";
        self::$info .= "<li style='height:40px;line-height:40px;font-size:20px;color:#333;word-break: break-all;'>错误文件：" . $file . "</li>";
        self::$info .= "<li style='height:40px;line-height:40px;font-size:20px;color:#333;word-break: break-all;'>错误行数：" . $line . "</li>";
        self::$info .= "<li style='height:40px;line-height:40px;font-size:20px;color:#333;word-break: break-all;'>";
        if (Debug) {
            $string = array_filter(explode("#", $detail));
            if (is_array($string)) {
                foreach ($string as $key => $value) {
                    self::$info .= '#' . $value . '<br>';
                }
            }
        }
        self::$info .= "</li>";
        self::$info .= "</ul></div>";
        //记录日志
        WriteLog($message);
    }
}
