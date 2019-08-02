<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class CsrfBuild extends Build {
    public function getPath(): string {
        return Common . '/csrf.php';
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php
/**
 * csrf配置表
 * 在html中调用_token()会生成csrf表单和值
 * 在js中调用_token(true)会生成csrf值
 */
use system\Route\CSRF;
//设置允许不进行CSRF验证的路由
CSRF::setAllow(array(
    //'/loginAction' , //对'/loginAction'这个路由不验证CSRF
));
EOT;

    }
}