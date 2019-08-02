<?php

namespace system\Command\Drivers;

use system\IO\Build\Task\ModelBuild;
use system\View;
use system\Command\CommandInterface;
use system\Command\CommandParse;

class ModelCommand implements CommandInterface {
    /**
     * 执行
     *
     * @param  [type] $argv [description]
     *
     * @return [type]       [description]
     */
    public function exec($argv = null) {
        return $this->generate($argv);
    }

    /**
     * 生成
     *
     * @param  [type] $argv [description]
     *
     * @return [type]       [description]
     */
    public function generate($argv = null) {
        $common = new CommandParse($argv);

        return $common->generateFile(Config('DEFAULT_MODEL_LAYER'), $this->getBuild());
    }

    /**
     * 获取帮助
     * @return string
     */
    public function getHelp() {
        return './make model test';
    }

    /**
     * 获取执行命令
     * @return string
     */
    public function getCommand() {
        return 'model';
    }

    /**
     * 获取Build
     * @return string
     */
    public function getBuild() {
        return ModelBuild::class;
    }

    public function requireName() {
        return true;
    }
}