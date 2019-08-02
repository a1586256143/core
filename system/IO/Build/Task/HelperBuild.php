<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class HelperBuild extends Build {
    public function getPath(): string {
        return Common . '/functions.php';
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php
/**
 * 在这里定义你的自定义函数
 */
EOT;

    }
}