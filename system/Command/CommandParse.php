<?php

namespace system\Command;

use system\IO\Build\Build;

class CommandParse {
    public $namesCount;
    public $namespace;
    public $names;
    public $dirname;
    public $path;
    public $className;
    public $action = '';

    /**
     * 构造方法
     *
     * @param array $argv 指令集
     */
    public function __construct($argv) {
        list(, $action, $name) = $argv;
        $this->action     = $action;
        $this->names      = explode('/', $name);
        $this->namesCount = count($this->names);
    }

    /**
     * 控制器初始化
     */
    public function getController() {
        $this->setValue(ControllerDIR);
    }

    /**
     * 模型初始化
     */
    public function getModel() {
        $this->setValue(ModelDIR);
    }

    /**
     * 参数封装
     *
     * @param string $value
     */
    private function setValue($value) {
        $this->dirname   = APP_DIR . $value;
        $this->namespace = $value;
        if ($this->namesCount > 1) {
            $names = $this->names;
            array_pop($names);
            $namespace       = implode('\\', $names);
            $this->namespace = $value . '\\' . $namespace;
        }
    }

    /**
     * 设置参数
     */
    private function setting() {
        $lastChild                 = $this->namesCount - 1;
        $this->className           = ucfirst($this->names[ $lastChild ]);
        $this->names[ $lastChild ] = $this->className;
        $this->names               = implode('/', $this->names);
        $this->path                = sprintf(_getFileName($this->dirname . "/%s"), $this->names);
    }

    /**
     * 生成文件
     */
    public function createData() {
        $basePath = dirname($this->path);
        if ($this->namesCount > 1 && !is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }
    }

    /**
     * 生成文件
     *
     * @param string $namespace 命名空间
     * @param string $buildType 生成类型名
     *
     * @return bool
     */
    public function generateFile($namespace, $buildType) {
        $this->setValue($namespace);
        // 设置参数
        $this->setting();
        // 生成文件
        $this->createData();
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
