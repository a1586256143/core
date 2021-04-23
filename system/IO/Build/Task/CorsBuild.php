<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class CorsBuild extends Build {
    public function getPath(): string {
        return Common . '/cors.php';
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php
return [
	'allowDefault' => [ // 默认配置
		'Origin' => '*' ,
	]
];
EOT;

    }
}