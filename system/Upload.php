<?php
/**
 * 上传处理
 * @author Major
 */

namespace system;
class Upload {
    public $path;                        //上传文件保存路径
    public $allowtype;                    //设置上传文件类型
    public $maxsize;                    //限制文件上传大小
    public $israndname;                    //设置是否随机重命名文件
    public $newFileName;                //新文件名
    public $errorNum = 0;                //错误号
    public $errorMess;                    //错误报告消息
    public $file;                    //错误报告消息
    public $fileName;
    public $fileDir;

    /**
     * 初始化
     *
     * @param file 上传名称 例如 values('files' , 'img')
     */
    public function __construct($file) {
        $this->path       = Config('UPLOAD_DIR');
        $this->allowtype  = explode(',', Config('UPLOAD_TYPE'));
        $this->maxsize    = Config('UPLOAD_MAXSIZE');
        $this->israndname = Config('UPLOAD_RANDNAME');
        //检查上传目录是否存在
        $this->checkFilePath();
        //初始化文件信息
        $this->file = $file;
    }

    /**
     * 文件上传
     * @author Major <1450494434@qq.com>
     */
    public function upload() {
        if (empty($this->file['tmp_name'])) {
            return $this->UploadError(-5);
        }
        //检测文件类型是否正确
        if (!$this->checkFileType()) {
            return $this->UploadError(-1);
        }
        if (!$this->checkSize()) {
            return $this->UploadError(-2);
        }
        $this->proRandName();

        return $this->start_upload();
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
            if (!@mkdir($this->path, 0777)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 设置随机文件名
     * @author Major <1450494434@qq.com>
     */
    public function proRandName() {
        $this->fileName = date('YmdHis') . "_" . rand(100, 999);
        $this->fileDir  = $this->path . '/' . date('Ymd');
        $patten         = '/(\.[a-z]+)/';
        preg_match($patten, $this->file['name'], $match);
        if (!is_dir($this->fileDir)) mkdir($this->fileDir);
        if (empty($match[0])) {
            $this->newFileName = $this->fileDir . '/' . $this->fileName . '.jpg';
        } else {
            $this->newFileName = $this->fileDir . '/' . $this->fileName . $match[0];
        }
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
    public function start_upload() {
        if (!is_uploaded_file($this->file['tmp_name'])) {
            return $this->UploadError(-3);
        }
        if (move_uploaded_file($this->file['tmp_name'], $this->newFileName)) {
            $this->file['path'] = ltrim($this->newFileName, '.');

            return $this->UploadError(1, $this->file);;
        } else {
            return $this->UploadError(-4);
        }
    }

    /**
     * 输出上传错误信息
     * @author Major <1450494434@qq.com>
     */
    public function UploadError($code, $info = array()) {
        switch ($code) {
            case 1 :
                $message = '上传成功！';
                break;
            case -1:
                $message = '上传类型不正确！';
                break;
            case -2:
                $message = '上传大小不能超过' . ceil($this->maxsize / 1024 / 1024) . 'M！';
                break;
            case -3:
                $message = '非法上传文件！';
                break;
            case -4:
                $message = '上传文件失败！';
                break;
            case -5:
                $message = '没有文件被上传！';
                break;
        }

        return array('msg' => $message, 'info' => $info);
    }
}