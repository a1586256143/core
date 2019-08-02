<?php

namespace system\IO\Storage\Drivers;

use system\IO\Storage\Storage;
use system\IO\Storage\StorageInterface;

class RedisStorage extends Storage implements StorageInterface {
    /**
     * @var $storage \Redis
     */
    protected static $storage;

    /**
     * 设置key
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function set($key, $value, $time = 0) {
        self::$storage->set($key, $value);
        $time && $this->expire($key, $time);

        return true;
    }

    /**
     * 删除
     *
     * @param $key
     *
     * @return int
     */
    public function remove($key) {
        if (!is_array($key)) {
            $key = [$key];
        }

        return self::$storage->del($key);
    }

    /**
     * 连接
     *
     * @param $config
     *
     * @return bool
     */
    public function connect($config = []) {
        if (self::$storage instanceof \Redis) {
            return self::$storage;
        }
        !$config['host'] && $config['host'] = Config('REDIS_HOST');
        if (!$config['host']) {
            $this->error = '未知的redis host';

            return false;
        }
        !$config['db'] && $config['db'] = Config('REDIS_DB');
        !$config['port'] && $config['port'] = Config('REDIS_PORT');
        if (!$this->loadExtension()) {
            $this->error = '没有找到redis扩展';

            return false;
        }
        $redis     = new \Redis();
        $isConnect = $redis->connect($config['host'], $config['port']);
        if (!$isConnect) {
            $this->error = $redis->getLastError();

            return false;
        }
        if ($config['pass'] && !$redis->auth($config['pass'])) {
            $this->error = $redis->getLastError();

            return false;
        }
        $redis->select($config['db']);
        self::$storage = $redis;

        return true;
    }

    /**
     * 获取存储的值
     *
     * @param $key
     *
     * @return bool|string
     */
    public function get($key) {
        return self::$storage->get($key);
    }

    /**
     * 是否加载了redis扩展
     * @return bool
     */
    public function loadExtension() {
        return extension_loaded('redis');
    }

    /**
     * 键是否存在
     *
     * @param $key
     *
     * @return mixed|void
     */
    public function exists($key) {
        if (!$key) {
            return false;
        }

        if (!self::$storage->exists($key)) {
            $this->error = '缓存不存在';

            return false;
        }

        return true;
    }

    /**
     * 设置过期时间
     *
     * @param     $key
     * @param int $time
     *
     * @return bool|mixed
     */
    public function expire($key, $time = 0) {
        if (!$this->exists($key)) {
            $this->error = '缓存不存在';

            return false;
        }
        self::$storage->expire($key, $time);

        return true;
    }

    /**
     * 清理当前数据库的所有数据
     * @return bool|mixed
     */
    public function clear() {
        self::$storage->flushDB();

        return true;
    }
}