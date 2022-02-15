<?php
/**
 * 上传处理
 * @author Major
 */

namespace system\Tool;

class Upload {
    public $path;                    //上传文件保存路径
    public $allowtype;               //设置上传文件类型
    public $maxsize;                 //限制文件上传大小
    public $file;                    //文件对象

    /**
     * 初始化
     *
     * @param array|string $file 上传的实例，包含tmp_name,name等原始信息
     * @param array  $config 上传配置
     */
    public function __construct($file, $config = []) {
        $tmp = is_bool($config) ? $config : $config['tmp'] ?: false;
        $upload          = $config['dir'] ?: Config('UPLOAD_DIR');
        $this->path      = $tmp ? $upload . '/tmp' : $upload;
        $this->allowtype = explode(',', $config['exts'] ?: Config('UPLOAD_TYPE'));
        $this->maxsize   = $config['maxsize'] ?: Config('UPLOAD_MAXSIZE');
        //初始化文件信息
        $this->file = $file;
    }

    /**
     * 文件上传
     * @author Major <1450494434@qq.com>
     */
    public function upload() {
        if (empty($this->file['tmp_name'])) {
            return $this->getErrorMsg(-5);
        }
        //检查上传目录是否存在
        if (!$this->checkFilePath()) {
            return $this->getErrorMsg(-6);
        }
        //检测文件类型是否正确
        if (!$this->checkFileType()) {
            return $this->getErrorMsg(-1);
        }
        if (!$this->checkSize()) {
            return $this->getErrorMsg(-2);
        }

        return $this->startUpload();
    }

    /**
     * 检测是否有存在文件上传的目录
     * @author Major <1450494434@qq.com>
     */
    public function checkFilePath() {
        if (empty($this->path)) {
            return false;
        }
        if (!file_exists($this->path) || !is_writable($this->path)) {
            if (!@mkdir($this->path, 0777, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 设置随机文件名
     * @author Major <1450494434@qq.com>
     * @param string $filename 文件名
     * @param string $uploaddir 上传的目录
     * @return string
     */
    public static function getRandName($filename = '', $uploaddir = '') {
        $dir       = $uploaddir . '/' . date('Ymd');
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (!is_dir($dir)) mkdir($dir);

        return $dir . '/' . sha1($filename) . '.' . $extension;
    }

    /**
     * 检测上传的文件是合法的类型
     * @author Major <1450494434@qq.com>
     */
    public function checkFileType() {
        return in_array($this->file['type'], $this->allowtype);
    }

    /**
     * 检测上传的文件是否允许的大小
     * @author Major <1450494434@qq.com>
     */
    public function checkSize() {
        return $this->file['size'] < $this->maxsize;
    }

    /**
     * 开始上传图片
     * @author Major <1450494434@qq.com>
     */
    public function startUpload() {
        $saveName = self::getRandName($this->file['name'], $this->path);
        if (!is_uploaded_file($this->file['tmp_name'])) {
            return $this->getErrorMsg(-3);
        }
        if (move_uploaded_file($this->file['tmp_name'], $saveName)) {
            $this->file['path'] = ltrim($saveName, '.');

            return $this->getErrorMsg(1, $this->file);
        } else {
            return $this->getErrorMsg(-4);
        }
    }

    /**
     * 从$path移动到$topath
     *
     * @param string $path   被移动的文件
     * @param string $topath 将要移动到的目录或文件
     * @return bool|string
     */
    public static function moveUpload($path, $topath = '') {
        if (!$topath) {
            $topath = self::getRandName($path, Config('UPLOAD_DIR'));
        }
        $path = ltrim($path , '/');
        $topath = ltrim($topath , '/');
        if (rename($path, $topath)) {
            return ltrim($topath, '.');
        }

        return false;
    }

    /**
     * 是否是临时目录
     *
     * @param string $path 路径
     *
     * @return bool
     */
    public static function isTmp($path = '') {
        if (strpos($path, 'tmp') !== false) {
            return true;
        }

        return false;
    }

    /**
     * 输出上传错误信息
     *
     * @param int   $code 错误代码
     * @param array $info 错误信息
     *
     * @author Major <1450494434@qq.com>
     * @return array
     */
    public function getErrorMsg($code, $info = []) {
        $message = '';
        switch ($code) {
            case 1 :
                $message = '上传成功';
                break;
            case -1:
                $message = '上传类型不正确';
                break;
            case -2:
                $message = '上传大小不能超过' . ceil($this->maxsize / 1024 / 1024) . 'M';
                break;
            case -3:
                $message = '非法上传文件';
                break;
            case -4:
                $message = '上传文件失败';
                break;
            case -5:
                $message = '没有文件被上传';
                break;
            case -6 :
                $message = '上传目录不存在';
                break;
        }

        return ['msg' => $message, 'info' => $info];
    }
}
