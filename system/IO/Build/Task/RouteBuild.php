<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class RouteBuild extends Build {
    public function getPath(): string {
        return Common . '/routes.php';
    }

    public function getBuildContent(): string {
        return <<< EOT
<?php
/**
 * 该页面为网站路由
 * 所有路由全部在Route::add，如果要新增继续往后追加即可
 * 对应的格式为
 * '访问路由' => '访问方法'
 */
use system\Route\Route;
Route::add(array(
    '/' => '\controllers\Index@index'
));
EOT;

    }
}