<?php
/**
 * 文件处理
 * @author Colin <15070091894@163.com>
 */

namespace system;
class File {
    protected        $file;
    protected static $instance;

    /**
     * 获取单例对象
     * @return \system\File
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 打开文件
     *
     * @param filename 文件名
     *
     * @author Colin <15070091894@163.com>
     */
    public function OpenFile($filename, $time = 0) {
        if ($time && (fileatime($filename) + $time) <= time()) {
            $this->DeleteFile($filename);

            return null;
        }
        //获取文件内容
        $file_resoule = file_get_contents($filename);
        if (!$file_resoule) {
            return null;
        }

        return $file_resoule;
    }

    /**
     * 打开目录
     *
     * @param path 要打开的目录
     *
     * @author Colin <15070091894@163.com>
     */
    public function OpenFileDir($path) {
        //打开目录
        $dir_soule = opendir($path);

        return $dir_soule;
    }

    /**
     * 清除目录内所有的数据
     *
     * @param path 要打开的目录
     *
     * @author Colin <15070091894@163.com>
     */
    public function ClearPathData($path) {
        //打开目录
        $dir_soule = $this->OpenFileDir($path);
        //读取目录内容
        while ($filename = readdir($dir_soule)) {
            //屏蔽. 和 .. 特殊操作符
            if (in_array($filename, array('.', '..'))) continue;
            //删除文件
            $this->DeleteFile($path . $filename);
        }
    }

    /**
     * 获取目录下的所有文件
     *
     * @param path 要打开的目录
     * @param path 返回指定格式的文件
     *
     * @author Colin <15070091894@163.com>
     */
    public function getDirAllFile($path = null, $suffix = null) {
        //打开目录
        $dir_soule = $this->OpenFileDir($path);
        while ($filename = readdir($dir_soule)) {
            $filepath = $path . '/' . $filename;
            //获取文件信息，主要获取文件后缀
            $info = pathinfo($filename);
            if (!empty($suffix)) {
                //屏蔽不是$suffix的文件
                if ($info['extension'] != $suffix) continue;
            }
            //屏蔽. 和 .. 特殊操作符
            if (in_array($filename, array('.', '..', '.DS_Store'))) continue;
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
     * @param filename 文件名
     * @param data 数据
     * @param isJson 是否json编码
     *
     * @author Colin <15070091894@163.com>
     */
    public function WriteFile($filename, $data, $isJson = true) {
        if ($isJson) {
            //对数据进行json编码
            $data = json_encode($data);
        }
        //生成$filename文件
        $fileobj = file_put_contents($filename, $data);
        if ($fileobj) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 追加文件
     *
     * @param filename 文件名
     * @param data 数据
     *
     * @author Colin <15070091894@163.com>
     */
    public function AppendFile($filename, $data) {
        $fopen = fopen($filename, 'a');
        fwrite($fopen, $data);
        fclose($fopen);
    }

    /**
     * 删除文件
     * @author Colin <15070091894@163.com>
     */
    public function DeleteFile($filename) {
        //删除文件
        return @unlink($filename);
    }

    /**
     * 获取上一层目录
     *
     * @param string $path       路径
     * @param string $endpath    遇到哪个目录停止
     * @param string $return_all 是否返回整条路径，如果为false 则返回上一层的文件夹名称
     *
     * @author Colin <15070091894@163.com>
     */
    public function getprev($path = null, $endpath = null, $return_all = false) {
        $preg_replace = preg_replace("/\\\\/", '/', $path);
        $path         = explode('/', $preg_replace);
        $pop          = array_pop($path);
        $endname      = array_pop($path);
        //处理遇到目录tingzhi
        $endpath    = explode('/', $endpath);
        $endpop     = array_pop($endpath);
        $merge_path = implode('/', $path);
        if ($endname == $endpop) {
            return null;
        }
        if (is_dir($merge_path)) {
            return $return_all ? $merge_path : $endname;
        }
    }

    /**
     * 显示一个文件管理系统
     *
     * @param string $path 访问路径
     *
     * @author Colin <15070091894@163.com>
     */
    public function fileManage($path = null, $param = null, $style = 'table') {
        $oldpath = $path;
        if (!empty($param)) {
            $path = $path . $param;
        }
        $path      = preg_replace('/@/', '/', $path);
        $parsepath = preg_replace('/@/', '/', $param);
        $prepath   = $this->getprev($path, $oldpath);
        if ($prepath) {
            $prepath = '@' . $prepath;
            if (!$param) {
                $prepath = '';
            }
        }
        $data['filearray'] = $this->getDirAllFile($path);
        $data['oldpath']   = $oldpath;
        $data['param']     = $param;
        $data['prepath']   = $prepath;
        $data['parsepath'] = $parsepath;

        return $data;
    }
}