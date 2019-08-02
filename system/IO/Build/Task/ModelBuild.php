<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class ModelBuild extends Build {
    public function getPath(): string {
        return $this->args['path'];
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php 
namespace {$this->args['namespace']};
use system\Model;
class {$this->args['name']} extends Model{

}
EOT;

    }
}