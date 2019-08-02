<?php

namespace system\Command\Drivers;

use system\IO\Build\Task\ControllerBuild;
use system\View;
use system\Command\CommandInterface;
use system\Command\CommandParse;

class ControllerCommand implements CommandInterface {

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

        return $common->generateFile(Config('DEFAULT_CONTROLLER_LAYER'), $this->getBuild());
    }

    /**
     * 获取帮助
     * @return string
     */
    public function getHelp() {
        return './make controller user';
    }

    /**
     * 获取执行命令
     * @return string
     */
    public function getCommand() {
        return 'controller';
    }

    /**
     * 获取Build
     * @return string
     */
    public function getBuild() {
        return ControllerBuild::class;
    }

    public function requireName() {
        return true;
    }
}