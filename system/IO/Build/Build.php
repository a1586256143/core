<?php

namespace system\IO\Build;

use system\Factory;
use system\IO\Build\Task\ControllerBuild;
use system\IO\Build\Task\CsrfBuild;
use system\IO\Build\Task\EnvBuild;
use system\IO\Build\Task\HelperBuild;
use system\IO\Build\Task\RouteBuild;
use system\IO\Build\Task\TemplateBuild;

class Build extends Factory {
    protected $build = [];
    // 生成时传递的参数
    protected $args = [];

    /**
     * 获取单例句柄
     * @return mixed
     */
    public static function getInstance($args = []) {
        return self::applyIns(self::class, new self($args));
    }

    /**
     * 初始化
     * Build constructor.
     *
     * @param $args
     */
    public function __construct($args = []) {
        if ($args) {
            $this->args = $args;
        }
    }

    /**
     * 获取路径
     * @return mixed
     */
    function getPath(): string {
        return '';
    }

    /**
     * 获取生成的内容
     * @return mixed
     */
    function getBuildContent(): string {
        return '';
    }

    /**
     * 执行Build
     * @return bool
     */
    public function exec() {
        $this->buildAppPath();
        $this->initBuild();
        $this->buildStart();

        return true;
    }

    /**
     * 开始生成
     */
    public function buildStart() {
        $file = Factory::File();
        foreach ($this->build as $key => $value) {
            $path    = $value->getPath();
            $content = $value->getBuildContent();
            !$file->isExsits($path) && $file->putFileContent($path, $content, false);
        }
    }

    /**
     * 添加一个Build
     *
     * @param                        $name
     * @param \system\IO\Build\Build $build
     *
     * @throws \system\MyError
     */
    public function addBuild($name, Build $build) {
        if (isset($this->build[ $name ]) && $this->build[ $name ]) {
            return true;
        }
        $this->build[ $name ] = $build;
    }

    /**
     * 初始化Build
     */
    protected function initBuild() {
        $this->addBuild('env', new EnvBuild());
        $this->addBuild('csrf', new CsrfBuild());
        $this->addBuild('helper', new HelperBuild());
        $this->addBuild('route', new RouteBuild());
        $this->addBuild('template', new TemplateBuild());
        $this->addBuild('controller', new ControllerBuild([
            'namespace' => Config('DEFAULT_CONTROLLER_LAYER'),
            'name'      => Config('DEFAULT_CONTROLLER'),
        ]));
    }

    /**
     * 生成APP的文件夹
     * @return bool
     */
    protected function buildAppPath() {
        //设置默认工作空间目录结构
        $dirnames = [
            'ControllerDIR' => APP_DIR . Config('DEFAULT_CONTROLLER_LAYER'),
            'ModelDIR'      => APP_DIR . Config('DEFAULT_MODEL_LAYER'),
            'ViewDIR'       => Config('TPL_DIR'),
        ];
        //根据数组的key 生成常量
        foreach ($dirnames as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
        //缓存文件夹
        $cache = rtrim(ltrim(Config('CACHE_DIR'), './'), './');
        //缓存临时文件
        $cacheTmp = rtrim(ltrim(Config('CACHE_DATA_DIR'), './'), './');
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS')) {
            $cache    = '/' . $cache;
            $cacheTmp = '/' . $cacheTmp;
        }
        if (!is_dir(APP_DIR)) {
            //更新文件权限
            shell_exec('chmod -R 0755 ' . APP_DIR);
        }
        //批量创建目录
        $dir = [
            APP_PATH,                //应用路径
            APP_DIR,                //系统app目录
            RunTime,                //运行目录
            ControllerDIR,
            ModelDIR,
            ViewDIR,
            $cache,                //缓存目录
            $cacheTmp,            //缓存临时文件
            Common,                //全局目录
            Library,                //第三方目录
        ];
        outdir($dir);

        return true;
    }
}