<?php

namespace system\Command;

use system\Factory;
use system\IO\Build\Build;

class CommandParse {
    public $namescount;
    public $namespace;
    public $names;
    public $dirname;
    public $path;
    public $className;
    public $action = '';

    /**
     * 构造方法
     *
     * @param [type] $argv [description]
     */
    public function __construct($argv) {
        list($cli, $action, $name) = $argv;
        $this->action     = $action;
        $this->names      = explode('/', $name);
        $this->namescount = count($this->names);
    }

    /**
     * 控制器初始化
     * @return [type] [description]
     */
    public function getController() {
        $this->setvalue(ControllerDIR);
    }

    /**
     * 模型初始化
     * @return [type] [description]
     */
    public function getModel() {
        $this->setvalue(ModelDIR);
    }

    /**
     * 参数封装
     *
     * @param  [type] $value [description]
     *
     * @return [type]        [description]
     */
    private function setvalue($value) {
        $this->dirname   = APP_DIR . $value;
        $this->namespace = $value;
        if ($this->namescount > 1) {
            $names = $this->names;
            array_pop($names);
            $namespace       = implode('\\', $names);
            $this->namespace = $value . '\\' . $namespace;
        }
    }

    /**
     * 设置参数
     * @return [type] [description]
     */
    private function seting() {
        $lastChild                 = $this->namescount - 1;
        $this->className           = ucfirst($this->names[ $lastChild ]);
        $this->names[ $lastChild ] = $this->className;
        $this->names               = implode('/', $this->names);
        $this->path                = sprintf(_getFileName($this->dirname . "/%s"), $this->names);
    }

    /**
     * 生成文件
     *
     * @param  [type] $dirname    [description]
     * @param  [type] $names      [description]
     *
     * @return [type]             [description]
     */
    public function createdata() {
        $basePath = dirname($this->path);
        if ($this->namescount > 1 && !is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }
    }

    /**
     * 生成文件
     *
     * @param string $dir 生成的目录
     *
     * @return bool
     */
    public function generateFile($namespace = null, $buildType) {
        $this->setvalue($namespace);
        // 设置参数
        $this->seting();
        // 生成文件
        $this->createdata();
        $file  = Factory::File();
        $build = Build::getInstance();
        $build->addBuild('module', new $buildType([
            'namespace' => $this->namespace,
            'name'      => $this->className,
            'path'      => $this->path,
        ]));
        $build->buildStart();

        return true;
    }
}