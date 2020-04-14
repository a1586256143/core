<?php

namespace system\IO\File;

use system\Factory;

class Log extends Factory {

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
            $msg = date('Y-m-d H:i:s') . ' --- ' . $msg;
            self::timeRecord();
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
     *
     * @param string $msg 日志内容
     *
     * @return bool
     */
    public static function generator($msg = null) {
        if (!Debug) {
            return true;
        }
        self::$file = Factory::File();
        $logDir     = Config('LOGDIR');
        //创建日志文件夹
        outdir($logDir);
        //日志文件名格式
        $logName = date('Y-m-d', time());
        //日志后缀
        $logSuffix = Config('LOG_SUFFIX');
        $logPath   = $logDir . '/' . $logName . $logSuffix;
        $msg && self::$logs[] = $msg;
        self::$logs[] = '[RunTime] ' . self::timeRecord(1);
        self::$logs[] = '[Memory] ' . self::memoryRecord();
        $logs         = implode(PHP_EOL, self::$logs);
        self::$file->appendFileContent($logPath, $logs . PHP_EOL . PHP_EOL);

        return true;
    }

    /**
     * 实现调用静态方法
     *
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments) {
        self::addRecord($arguments[0], false, strtoupper($name));
    }

    /***
     * 记录执行时间
     *
     * @param int $mode
     *
     * @return string
     */
    public static function timeRecord($mode = 0, $name = 'app') {
        static $timestamp;
        if (!$mode) {
            $timestamp[ $name ] = microtime();

            return '';
        }
        $endTimestamp[ $name ] = microtime();
        $start                 = array_sum(explode(" ", $timestamp[ $name ]));
        $end                   = array_sum(explode(" ", $endTimestamp[ $name ]));

        return sprintf("%.4f s", ($end - $start));
    }

    /**
     * 记录内存消耗
     * @return string
     */
    public static function memoryRecord() {
        return sprintf("%.2f k", (memory_get_usage() / 1024));
    }
}
