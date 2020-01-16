<?php

namespace system\Command\Drivers;

use system\Command\CommandInterface;
use system\Command\CommandParse;
use system\IO\Build\Task\ModelBuild;

class ModelCommand implements CommandInterface {
    /**
     * 执行
     *
     * @param array $argv 指令集
     *
     * @return mixed
     */
    public function exec($argv = null) {
        return $this->generate($argv);
    }

    /**
     * 生成
     *
     * @param  [type] $argv [description]
     *
     * @return bool
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
