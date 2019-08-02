<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class TemplateBuild extends Build {
    public function getPath(): string {
        return Common . '/template.php';
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php
//此文件为模板中使用的__常量名__格式配置文件，配置格式为
//if(!defined('常量名')) define('常量名' , '常量值');
if(!defined('__URL__')) define('__URL__' , getCurrentUrl());
if(!defined('__PUBLIC__')) define('__PUBLIC__' , Config('PUBLIC_DIR'));
EOT;

    }
}