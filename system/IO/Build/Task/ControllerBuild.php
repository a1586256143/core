<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class ControllerBuild extends Build {
    public function getPath(): string {
        if (isset($this->args['path']) && $this->args['path']) {
            return $this->args['path'];
        }

        return _getFileName(ControllerDIR . '/' . Config('DEFAULT_CONTROLLER'));
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php 
namespace {$this->args['namespace']};
use system\Base;
class {$this->args['name']} extends Base{
    public function index(){
        return 'Welcome to use MyClassPHP';
    }
}

EOT;

    }
}