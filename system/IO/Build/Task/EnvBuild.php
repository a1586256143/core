<?php

namespace system\IO\Build\Task;

use system\IO\Build\Build;

class EnvBuild extends Build {
    public function getPath(): string {
        return Common . '/.env';
    }

    public function getBuildContent(): string {
        return <<< EOT
[config]
DB_HOST=localhost   ;数据库地址
DB_TYPE=mysqli      ;数据库类型
DB_TABS=test        ;数据表名
DB_USER=root        ;数据库用户
DB_PASS=你的密码     ;数据库密码
EOT;

    }
}