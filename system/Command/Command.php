<?php

namespace system\Command;

use system\Factory;
use system\Facade;
use system\Command\Drivers\ControllerCommand;
use system\Command\Drivers\ModelCommand;

class Command extends Facade {

    protected static $container;

    protected static $argv;

    // 所有可输入的命令列表
    protected static $helpCommands = [
        'controller',
        'model',
        'help',
        'version',
    ];
    // 不是命令的元素
    protected static $notCommand = [
        'help',
    ];
    protected static $action;
    protected static $name;

    /**
     * 初始化
     *
     * @param [type] $argv [description]
     */
    public function __construct($argv) {
        self::$container = Factory::Container();
        self::$argv      = $argv;
    }

    /**
     * 获取门面名
     * @return [type] [description]
     */
    public static function getFacadeAccessor() {
        return self::$container->make(self::$argv[1], self::$argv);
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
        if (!self::$name && $command::requireName()) {
            self::putMsg('command faild , sample ' . $command::getHelp());
        }
        $result = $command::exec(self::$argv);
        is_bool($result) && self::putMsg('exec success');
        is_string($result) && self::putMsg($result);

        return self::putMsg('exec fail');
    }

    /**
     * 指令校验
     * @return [type] [description]
     */
    public static function commandValidate() {
        list($cli, $action, $name) = self::$argv;
        $helps = self::bindContainer();
        if (!$action || !in_array($action, self::$helpCommands) || in_array($action, self::$notCommand)) {
            self::echoHelp($helps);

            return false;
        }
        self::$action = $action;
        self::$name   = $name;

        return true;
    }

    /**
     * 输出字符串
     *
     * @param $msg
     */
    public static function putMsg($msg) {
        echo $msg . PHP_EOL;
        exit();
    }

    /**
     * 输出帮助信息
     *
     * @param $helps
     */
    protected static function echoHelp($helps) {
        echo 'Usage ./make [ options ] [ args ]' . PHP_EOL;
        foreach ($helps as $value) {
            echo "\r -" . $value['command'] . "\r\n\t" . $value['help'] . PHP_EOL;
        }
        self::putMsg('');
    }

    /**
     * 绑定容器
     */
    protected static function bindContainer() {
        $helpCommand = array_diff(self::$helpCommands, self::$notCommand);
        $helps       = [];
        foreach ($helpCommand as $value) {
            $name    = 'system\Command\Drivers\\' . $value . 'Command';
            $command = new $name();
            $helps[] = [
                'command' => $command->getCommand(),
                'help'    => $command->getHelp()
            ];
            self::$container->bind($value, function () use ($name) {
                return $name;
            });
        }

        return $helps;
    }
}