<?php

namespace system\IO\Storage\Drivers;

use system\IO\Storage\Storage;
use system\IO\Storage\StorageInterface;
use system\MyError;

class RedisStorage extends Storage implements StorageInterface {
    /**
     * @var $storage \Redis
     */
    protected static $storage;

    /**
     * 设置key
     *
     * @param string       $key   保存名
     * @param string|array $value 保存值
     * @param int          $time  过期时间，单位秒
     *
     * @return bool
     * @throws
     */
    public function set($key, $value, $time = 0) {
        if (is_array($value)) {
            throw new MyError('Expected value mismatch, character required');
        }
        self::$storage->set($key, $value);
        $time && $this->expire($key, $time);

        return true;
    }

    /**
     * 删除
     *
     * @param string $key 名称
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
     * @param array $config 配置
     *
     * @return mixed
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
        !$config['pass'] && $config['pass'] = Config('REDIS_PASS');
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
     * @param string $key 名称
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
     * @param string $key 名称
     *
     * @return bool
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
     * @param string $key  名称
     * @param int    $time 过期时间，单位秒
     *
     * @return bool
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
     * @return bool
     */
    public function clear() {
        self::$storage->flushDB();

        return true;
    }
}
