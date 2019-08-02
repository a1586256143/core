<?php

namespace system\IO\Storage;
interface StorageInterface {
    /**
     * 设置一个值
     *
     * @param $key
     * @param $value
     * @param $time
     *
     * @return mixed
     */
    public function set($key, $value, $time = 0);

    /**
     * 获取
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * 删除
     *
     * @param $key
     *
     * @return mixed
     */
    public function remove($key);

    /**
     * 连接
     *
     * @param array $config
     *
     * @return mixed
     */
    public function connect($config = []);

    /**
     * 缓存是否存在
     *
     * @param $key
     *
     * @return mixed
     */
    public function exists($key);

    /**
     * 设置过期时间
     *
     * @param $key
     *
     * @return mixed
     */
    public function expire($key, $time = 0);

    /**
     * 清理所有的信息
     * @return mixed
     */
    public function clear();
}