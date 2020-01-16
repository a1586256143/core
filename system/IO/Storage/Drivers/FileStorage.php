<?php

namespace system\IO\Storage\Drivers;

use system\Factory;
use system\IO\Storage\Storage;
use system\IO\Storage\StorageInterface;

class FileStorage extends Storage implements StorageInterface {
    /**
     * @var $storage \system\IO\File\File
     */
    protected static $storage;

    /**
     * 连接
     *
     * @param array $config 配置值
     *
     * @return bool|mixed
     */
    public function connect($config = []) {
        $config['savePath'] = Config('CACHE_DATA_DIR');
        $config['prefix']   = Config('CACHE_OUT_PREFIX');
        $config['suffix']   = Config('CACHE_OUT_SUFFIX');
        $this->config       = $config;
        $this->_checkPath();
        /**
         * @var \system\IO\File\File
         */
        self::$storage = Factory::File();

        return true;
    }

    /**
     * 删除文件
     *
     * @param string $key 名称
     *
     * @return bool
     */
    public function remove($key) {
        if (!$this->exists($key)) {
            return false;
        }
        $name = $this->_generateFileName($key);

        return self::$storage->removeFile($name);
    }

    /**
     * 获取一个数据
     *
     * @param string $key 名称
     *
     * @return mixed
     */
    public function get($key) {
        $name = $this->_generateFileName($key);
        $data = self::$storage->getFileContent($name);
        if ($data) {
            $data = unserialize(json_decode($data, true));
            if ($data['expire'] && $data['expire'] < time()) {
                self::$storage->removeFile($key);

                return '';
            }
        }

        return $data['data'];
    }

    /**
     * 设置一个值
     *
     * @param string       $key   保存名称
     * @param string|array $value 保存值
     * @param int          $time  过期时间，单位秒
     *
     * @return bool|mixed
     */
    public function set($key, $value, $time = 0) {
        $name = $this->_generateFileName($key);
        $data = [
            'data'   => $value,
            'expire' => $time ? (time() + $time) : 0,
        ];
        $data = serialize($data);

        return self::$storage->putFileContent($name, $data, true);
    }

    /**
     * 是否存在
     *
     * @param string $key 名称
     *
     * @return bool
     */
    public function exists($key) {
        $name = $this->_generateFileName($key);
        if (!self::$storage->isExists($name)) {
            $this->error = '文件不存在';

            return false;
        }

        return true;
    }

    /**
     * 设置有效期
     *
     * @param string $key  名称
     * @param int    $time 过期时间，单位秒
     *
     * @return bool
     */
    public function expire($key, $time = 0) {
        $name = $this->_generateFileName($key);
        if (!$this->exists($name)) {
            return false;
        }
        $data = $this->get($name);
        if (!$data) {
            return false;
        }
        $data = [
            'data'   => $data,
            'expire' => time() + $time,
        ];
        self::$storage->putFileContent($name, $data);

        return true;
    }

    /**
     * 清理
     * @return bool
     */
    public function clear() {
        self::$storage->removeAll($this->config['savePath']);

        return true;
    }

    /**
     * 检查路径
     * @return bool
     */
    protected function _checkPath() {
        if (!$this->config['savePath']) {
            return true;
        }
        if (!is_dir($this->config['savePath'])) {
            $this->error = '临时目录不存在';

            return false;
        }
        if (!is_writable($this->config['savePath']) || !is_readable($this->config['savePath'])) {
            $this->error = '临时目录权限不足';

            return false;
        }

        return true;
    }

    /**
     * 生成文件名
     *
     * @param string $name 名称
     *
     * @return string
     */
    protected function _generateFileName($name) {
        if (empty($this->config['prefix'])) {
            $this->config['prefix'] = substr(date('Y'), 2, 2) . '_';
        }
        //生成文件名
        $fileName = $this->config['prefix'] . md5($this->config['prefix'] . $name) . $this->config['suffix'];

        //组合地址
        return $this->config['savePath'] . $fileName;
    }
}
