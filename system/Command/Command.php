<?php

namespace system\Command;

use system\Factory;
use system\Facade;
use system\Command\Dirvers\ControllerCommand;
use system\Command\Dirvers\ModelCommand;

class Command extends Facade {

    protected static $container;

    protected static $argv;

    protected static $helps = [];

    /**
     * 初始化
     *
     * @param [type] $argv [description]
     */
    public function __construct($argv) {
        self::$container = Factory::Container();
        $this->run();
        self::$argv = $argv;
    }

    /**
     * 获取门面名
     * @return [type] [description]
     */
    public static function getFacadeAccessor() {
        return self::$container->make(self::$argv[1], self::$argv);
    }

    /**
     * 运行方法
     * @return [type] [description]
     */
    public function run() {
        self::$container->bind('controller', function () {
            return ControllerCommand::class;
        });
        self::$container->bind('model', function () {
            return ModelCommand::class;
        });
    }

    /**
     * 生成
     *
     * @param  Facade $command [description]
     *
     * @return [type]          [description]
     */
    public static function generate(Facade $command) {
        $status = self::commandValidate();
        if (!$status) {
            return false;
        }
        $result = $command::exec(self::$argv);
        if ($result) {
            return 'create success' . PHP_EOL;
        }

        return 'create fail' . PHP_EOL;
    }

    /**
     * 指令校验
     * @return [type] [description]
     */
    public static function commandValidate() {
        list($cli, $action, $name) = self::$argv;
        if (!$action) {
            return false;
        }

        return true;
    }
}