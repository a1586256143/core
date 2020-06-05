<?php

namespace system\Command\Drivers;

use system\Command\CommandInterface;

class RunCommand implements CommandInterface {
    public function exec($argv = null) {
        $args = $this->getArgs($argv);
        $host = $args['h'] ?? '127.0.0.1';
        $port = $args['p'] ?? '8080';
        $command = 'php -S %s:%s -t %s';
        passthru(sprintf($command , $host , $port ,APP_DIR . 'public'));
    }

    public function generate() {
        return '';
    }

    public function getCommand() {
        return 'run';
    }

    public function getHelp() {
        return './mark run';
    }

    public function requireName() {
        return false;
    }

    protected function getArgs($argv){
        $argv = array_slice($argv , 2);
        $argv = array_chunk($argv , 2);
        foreach ($argv as $key => $val){
            list($prefix , $value) = $val;
            $argvs[str_replace('-' , '' , $prefix)] = $value;
        }
        return $argvs;
    }
}