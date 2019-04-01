<?php

namespace system;
class Log {

    protected static $file;
    // 日志内容器
    protected static $logs;

    /**
     * 添加记录条数
     *
     * @param null   $msg    记录信息
     * @param bool   $time   需要记录时间
     * @param string $prefix 前缀
     */
    public static function addRecord($msg = null, $time = false, $prefix = null) {
        if ($time) {
            $msg = Date::setDate(null, time()) . ' --- ' . $msg;
        }
        if ($prefix) {
            $msg = '[' . $prefix . '] ' . $msg;
        }
        self::$logs[] = $msg;
    }

    /**
     * 生成debug日志
     *
     * @param null $msg 日志内容
     */
    public static function debug($msg = null) {
        self::addRecord($msg, false, 'DEBUG');
    }

    /**
     * 生成日志
     * @return bool
     */
    public static function generator() {
        if (!Debug) {
            return true;
        }
        self::$file = File::getInstance();
        $logDir     = Config('LOGDIR');
        //创建日志文件夹
        outdir($logDir);
        //日志文件名格式
        $logName = date('Y-m-d', time());
        //日志后缀
        $logSuffix = Config('LOG_SUFFIX');
        $logPath   = $logDir . '/' . $logName . $logSuffix;
        $logs      = implode(PHP_EOL, self::$logs);
        self::$file->AppendFile($logPath, $logs . PHP_EOL . PHP_EOL, false);
    }

    public function __callStatic($name, $arguments) {
        self::addRecord($arguments[0], false, strtoupper($name));
    }
}