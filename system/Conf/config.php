<?php
/*
    Author : Colin,
    Creation time : 2015-8-1
    FileType : 配置文件
    FileName : config.php
*/
return [
    //数据库配置信息
    'DB_TYPE'                  => 'mysqli',                //数据库类型(mysql,mysqli,pdo)
    'DB_HOST'                  => 'localhost',            //主机地址
    'DB_USER'                  => '',                        //用户名
    'DB_PASS'                  => '',                        //密码
    'DB_TABS'                  => '',                        //数据库名称
    'DB_PREFIX'                => '',                        //数据表前缀
    'DB_CODE'                  => 'UTF8',                    //数据库编码
    'DB_PORT'                  => '3306',                   //数据库端口

    // 路由设置
    'CSRF'                     => false,        // 是否开启CSRF

    //目录设置
    'PUBLIC_DIR'               => '/static',    //公共文件地址

    //session设置
    'SESSION_START'            => true,                        //开启session

    //控制器设置
    'DEFAULT_CONTROLLER_LAYER' => 'app',   //默认控制器目录名
    'DEFAULT_CLASS_SUFFIX'     => '.php',    //默认类文件后缀
    'DEFAULT_CONTROLLER'       => 'Index', // 默认控制器
    'DEFAULT_METHOD'           => 'index',
	'DEFAULT_MODULE'		   => '' , // 默认模块

    //模型设置
    'DEFAULT_MODEL_LAYER'      => 'models',         //默认模型目录名

    //模板引擎设置
    'TPL_MODEL'                => 'smartyBC',                    //模板引擎
    'TPL_TYPE'                 => '.html',                //模板类型
    'TPL_DIR'                  => APP_DIR . 'views/',                //模板文件存放目录
    'TPL_C_DIR'                => RunTime . '/templates_c/',    //编译文件存放目录
    'TPL_CACHE'                => RunTime . '/templates_cache/',      //编译文件文件目录
    'TPL_CONFIG'               => [
        'template_dir' => 'TPL_DIR',
        'compile_dir'  => 'TPL_C_DIR',
        'cache_dir'    => 'TPL_CACHE'
    ],                    //模板配置

    //缓存设置
    'CACHE_DIR'                => RunTime . '/caches/', //缓存文件夹
    'CACHE_DATA_DIR'           => RunTime . '/caches/tmp/', //S缓存文件存放目录
    'CACHE_OUT_PREFIX'         => 'tmp_',                //缓存文件名生成规则
    'CACHE_OUT_SUFFIX'         => '.json',                //缓存存储后缀

    //日志设置
    'LOG'                      => true, // 记录日志信息
    'LOGDIR'                   => RunTime . '/logs',        //日志文件夹
    'LOG_SQL'                  => true, // SQL记录开启
    'LOG_SUFFIX'               => '.log',                //日志后缀

    //上传配置
    'UPLOAD_DIR'               => './uploads',                //上传文件的目录
    'UPLOAD_TYPE'              => 'image/jpg,image/jpeg,image/png,image/gif',            //上传文件类型
    'UPLOAD_MAXSIZE'           => 2097152,                   //上传文件大小

    //验证码配置
    'CODE_CHARSET'             => 'abcdefghkmnprstuvwxyz23456789',//验证码随机因子
    'CODE_LENGTH'              => 4,                                //验证码长度
    'CODE_WIDTH'               => 130,                                //验证码宽度
    'CODE_HEIGHT'              => 50,                                //验证码高度
    'CODE_FONTSIZE'            => 20,                                //验证码字体大小
    'CODE_FONTPATH'            => Core . 'Tool/elephant.ttf',//验证码字体文件存放路径

    //时间配置
    'DATE_DEFAULT_TIMEZONE'    => 'PRC',                //默认时区

    //权限配置
    'AUTH_OTHER'               => true,                   //是否验证其他不在规则表方法

    //错误处理
    'ERROR_MESSAGE'            => '500，此网站可能正在维护~~~', //当Debug关闭，网页错误时提示信息

    //扩展配置
    'ADDON_PATH'               => 'addons', // 扩展目录

    'STORAGE_TYPE' => 'file',
    'REDIS_HOST'   => '127.0.0.1',
    'REDIS_DB'     => 0,
    'REDIS_PORT'   => 6379,
    'REDIS_PASS'   => '',

    'TIMEZONE'     => 'Asia/Shanghai',

    // success和error的默认响应码
    'SUCCESS_CODE' => 200,
    'ERROR_CODE'   => 404,

];
