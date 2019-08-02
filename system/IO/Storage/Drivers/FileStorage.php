<?php

namespace system\IO\Storage\Drivers;

use system\Factory;
use system\IO\Storage\Storage;
use system\IO\Storage\StorageInterface;

class FileStorage extends Storage implements StorageInterface {
    /**
     * @var $storage \system\File
     */
    protected static $storage;

    /**
     * 连接
     *
     * @param array $config
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
     * @param $key
     *
     * @return mixed|void
     */
    public function remove($key) {
        if (!$this->exists($key)) {
            return false;
        }
        $name = $this->_generFileName($name);

        return self::$storage->removeFile($name);
    }

    /**
     * 获取一个数据
     *
     * @param $key
     *
     * @return false|mixed|string|null
     */
    public function get($key) {
        $name = $this->_generFileName($key);
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
     * @param $key
     * @param $value
     *
     * @return bool|mixed
     */
    public function set($key, $value, $time = 0) {
        $name = $this->_generFileName($key);
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
     * @param $key
     *
     * @return mixed|void
     */
    public function exists($key) {
        $name = $this->_generFileName($key);
        if (!self::$storage->isExsits($name)) {
            $this->error = '文件不存在';

            return false;
        }

        return true;
    }

    /**
     * 设置有效期
     *
     * @param     $key
     * @param int $time
     *
     * @return mixed|void
     */
    public function expire($key, $time = 0) {
        $name = $this->_generFileName($key);
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
     * @return mixed|void
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
     * @param $name
     *
     * @return string
     */
    protected function _generFileName($name) {
        if (empty($this->config['prefix'])) {
            $this->config['prefix'] = substr(date('Y'), 2, 2) . '_';
        }
        //生成文件名
        $fileName = $this->config['prefix'] . md5($this->config['prefix'] . $name) . $this->config['suffix'];

        //组合地址
        return $this->config['savePath'] . $fileName;
    }
}