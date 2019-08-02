<?php

namespace system\Command;
interface CommandInterface {
    /**
     * 执行
     * @return mixed
     */
    public function exec();

    /**
     * 生成
     * @return mixed
     */
    public function generate();

    /**
     * 获取帮助
     * @return mixed
     */
    public function getHelp();

    /**
     * 获取命令
     * @return mixed
     */
    public function getCommand();

    /**
     * 必须name
     * @return mixed
     */
    public function requireName();
}