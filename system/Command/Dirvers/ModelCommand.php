<?php

namespace system\Command\Dirvers;

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

        return $common->generateFile(Config('DEFAULT_MODEL_LAYER'));
    }

    /**
     * 获取帮助
     * @return string
     */
    public function getHelp() {
        return '创建模型 使用方法为 php make.php model test';
    }

    /**
     * 获取执行命令
     * @return string
     */
    public function getCommand() {
        return 'model';
    }
}