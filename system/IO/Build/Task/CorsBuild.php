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
		'Origin' => [] , // 允许的域名
		'Headers' => [ // 允许携带的额外字段头
			// 'Access-Token' , // 例如Access-Token
		]
	]
];
EOT;

    }
}