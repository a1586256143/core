<?php
/**
 * 文件处理
 * @author Colin <15070091894@163.com>
 */

namespace system\IO\File;

use system\Factory;

class File extends Factory {
    protected        $file;
    protected static $instance;

    /**
     * 打开文件
     *
     * @param string $filename 文件名
     * @param int    $time     保存时间，单位秒
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    public function getFileContent($filename, $time = 0) {
        if ($time && (fileatime($filename) + $time) <= time()) {
            $this->removeFile($filename);

            return null;
        }
        //获取文件内容
        $fileResource = file_get_contents($filename);
        if (!$fileResource) {
            return null;
        }

        return $fileResource;
    }

    /**
     * 打开目录
     *
     * @param string $path 要打开的目录
     *
     * @author Colin <15070091894@163.com>
     * @return resource|bool
     */
    public function getDirFiles($path) {
        //打开目录
        return opendir($path);
    }

    /**
     * 清除目录内所有的数据
     *
     * @param string $path 要打开的目录
     *
     * @author Colin <15070091894@163.com>
     */
    public function removeAll($path) {
        //打开目录
        $dirResource = $this->getDirFiles($path);
        //读取目录内容
        while ($filename = readdir($dirResource)) {
            //屏蔽. 和 .. 特殊操作符
            if (in_array($filename, ['.', '..'])) continue;
            //删除文件
            $this->removeFile($path . $filename);
        }
    }

    /**
     * 获取目录下的所有文件
     *
     * @param string $path   要打开的目录
     * @param string $suffix 返回指定格式的文件
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function getDirAllFile($path = null, $suffix = null) {
        //打开目录
        $dirResource = $this->getDirFiles($path);
        $file        = [];
        while ($filename = readdir($dirResource)) {
            $filepath = $path . '/' . $filename;
            //获取文件信息，主要获取文件后缀
            $info = pathinfo($filename);
            if (!empty($suffix)) {
                //屏蔽不是$suffix的文件
                if ($info['extension'] != $suffix) continue;
            }
            //屏蔽. 和 .. 特殊操作符
            if (in_array($filename, ['.', '..', '.DS_Store'])) continue;
            if (is_dir($filepath)) {
                //如果是文件夹则递归
                $file[ $filename ] = $this->getDirAllFile($filepath);
            } else {
                $file[ $filename ] = $filepath;
            }
        }

        return $file;
    }

    /**
     * 写入文件
     *
     * @param string       $filename 文件名
     * @param string|array $data     数据
     * @param bool         $isJson   是否json编码
     *
     * @author Colin <15070091894@163.com>
     * @return bool
     */
    public function putFileContent($filename, $data, $isJson = true) {
        if ($isJson) {
            //对数据进行json编码
            $data = json_encode($data);
        }
        //生成$filename文件
        $status = file_put_contents($filename, $data);
        if ($status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 追加文件
     *
     * @param string       $filename 文件名
     * @param string|array $data     数据
     *
     * @author Colin <15070091894@163.com>
     */
    public function appendFileContent($filename, $data) {
        $f = fopen($filename, 'a');
        fwrite($f, $data);
        fclose($f);
    }

    /**
     * 删除文件
     *
     * @param string $filename 文件名
     *
     * @author Colin <15070091894@163.com>
     * @return bool
     */
    public function removeFile($filename) {
        //删除文件
        return @unlink($filename);
    }

    /**
     * 是否是一个文件
     *
     * @param string $filename 文件名
     *
     * @return bool
     */
    public function isExists($filename) {
        if (!is_file($filename)) {
            return false;
        }

        return true;
    }
}
