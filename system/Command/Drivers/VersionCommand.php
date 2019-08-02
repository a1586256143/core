<?php

namespace system\Command\Drivers;

use system\Command\CommandInterface;

class VersionCommand implements CommandInterface {
    public function exec() {
        return VERSION;
    }

    public function generate() {
        return VERSION;
    }

    public function getCommand() {
        return 'version';
    }

    public function getHelp() {
        return './make version';
    }

    public function requireName() {
        return false;
    }
}