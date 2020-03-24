<?php
/*
    Author : Colin,
    Creation time : 2015-8-1
    FileType : 函数库
    FileName : function.php
*/

/**
 * 返回json数据
 *
 * @param mixed 要返回的数据
 *
 * @author Colin <15070091894@163.com>
 */
function ajaxReturn($array = null) {
    header('content-type:application/json');

    return json_encode($array, JSON_UNESCAPED_UNICODE);
}

/**
 * 返回成功状态
 *
 * @param mixed $msg  提示信息
 * @param int   $code 代码值
 *
 * @return string
 */
function success($msg, $code = 200) {
    $item = ['code' => $code, 'msg' => $msg];
    if (is_array($msg)) {
        $item['data'] = $msg;
        unset($item['msg']);
    }

    return ajaxReturn($item);
}

/**
 * 返回失败|错误状态
 *
 * @param mixed $msg  提示信息
 * @param int   $code 代码值
 *
 * @return string
 */
function error($msg, $code = 404) {
    $item = ['code' => $code, 'msg' => $msg];

    return ajaxReturn($item);
}

/**
 * M方法实例化模型
 *
 * @param string $name 模型名称
 *
 * @author Colin <15070091894@163.com>
 * @return \system\Model
 */
function M($name = null) {
    return system\Factory::CreateSystemModel($name);
}

/**
 * E方法对错误进行提醒
 *
 * @param string $message 地址
 *
 * @author Colin <15070091894@163.com>
 * @throws
 */
function E($message) {
    $debug = Debug;
    //记录日志
    \system\IO\File\Log::generator($message);
    if (AJAX) {
        error($message);
    }
    if ($debug) {
        throw new \system\MyError($message);
    } else {
        throw new \system\MyError(Config('ERROR_MESSAGE'));
    }
}

/**
 * 引入常规文件
 *
 * @param string|array $path    文件路径
 * @param string       $modules 加载的模块
 * @param bool         $return  是否需要返回值
 *
 * @author Colin <15070091894@163.com>
 * @return string|array
 */
function require_file($path, $modules = '', $return = true) {
    $content = [];
    if (is_array($path)) {
        foreach ($path as $key => $value) {
            if (file_exists($modules . $value)) {
                $content[] = require_once $modules . $value;
            }
        }

        return $content;
    } else if (is_string($path)) {
        if (file_exists($path)) {
            if ($return) {
                return require_once $path;
            } else {
                require_once $path;
            }

        }
    }
}

/**
 * 合并配置值
 *
 * @param string $name1 第一个需合并的数组
 * @param string $name2 第二个需合并的数组
 * @param string $name3 第三个需合并的数组
 *
 * @author Colin <15070091894@163.com>
 * @return array
 */
function replace_recursive_params($name1, $name2 = null, $name3 = null) {
    $var1 = require_file($name1);
    $var2 = require_file($name2);
    $var3 = require_file($name3);
    if (empty($var2) && empty($var3)) {
        return $var1;
    } else if (empty($var3)) {
        return array_replace_recursive($var1, $var2);
    } else if (!empty($var3)) {
        $merge = array_replace_recursive($var1, $var3);

        return array_replace_recursive($merge, $var2);
    } else {
        return $var1;
    }
}

/**
 * 创建文件夹 支持批量创建
 *
 * @param string $param 文件夹数组
 *
 * @author Colin <15070091894@163.com>
 * @return array
 */
function outdir($param) {
    $result = [];
    if (is_array($param)) {
        foreach ($param as $key => $value) {
            if (!is_dir($value)) {
                $result[ $value ] = mkdir($value, 0777);
            }
        }
    } else if (is_string($param)) {
        if (!is_dir($param)) $result[ $param ] = mkdir($param, 0777);
    }

    return $result;
}

/**
 * 打印输出函数
 *
 * @param array 要被打印的数据
 *
 * @author Colin <15070091894@163.com>
 */
function dump($array) {
    header('Content-type:text/html;charset="UTF-8"');
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

/**
 * 生成url
 * @return string
 */
function url($url) {
    return getSiteUrl(false) . '/' . ltrim($url, '/');
}

/**
 * 设置session
 *
 * @param string $name  session的名称
 * @param string $value session要保存的值
 *
 * @author Colin <15070091894@163.com>
 * @throws
 * @return mixed
 */
function session($name = '', $value = '') {
    if (Config('SESSION_START')) {
        //session名称为空 返回所有
        if ($name === '') {
            return $_SESSION;
        } else if ($name == 'null') {//清空session
            return session_destroy();
        } else if (!empty($name) && $value === '') {//session值为空
            return $_SESSION["$name"] !== 'null' ? $_SESSION["$name"] : null;
        } else if (!empty($name) && !empty($value)) {    //session名称和值都不为空
            $_SESSION["$name"] = $value;
        } else if (!empty($name) && is_null($value)) {
            unset($_SESSION["$name"]);
        }
    } else {
        throw new \system\MyError('session未打开！请检查配置文件');
    }

    return '';
}

/**
 * 接收post和get值函数
 *
 * @param string $type     要获取的POST或GET
 * @param string $formname 要获取的POST或type的表单名
 * @param string $function 要使用的函数
 * @param string $default  默认值
 *
 * @author Colin <15070091894@163.com>
 * @return string|array
 */
function values($type, $formname = null, $function = 'trim', $default = null) {
    $string = '';
    switch ($type) {
        case 'get':
            $string = isset($_GET[ $formname ]) ? $_GET[ $formname ] : '';
            break;
        case 'get.':
            $string = $_GET;
            break;
        case 'post':
            $string = isset($_POST[ $formname ]) ? $_POST[ $formname ] : '';
            break;
        case 'post.':
            $string = $_POST;
            break;
        case 'files':
            $string = isset($_FILES[ $formname ]) ? $_FILES[ $formname ] : '';
            break;
        case 'files.':
            $string = $_FILES;
            break;
        case 'request':
            $string = $_REQUEST[ $formname ];
            break;
    }
    if ($function == null) {
        return $string;
    }
    //解析函数，得到函数名
    $function   = explode(',', $function);
    $processing = is_array($string) ? [] : '';
    if (is_array($string)) {
        $processing = [];
        foreach ($string as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    //对得到的值 使用函数处理
                    foreach ($function as $fk => $fv) {
                        $v                        = $fv($v);
                        $processing[ $key ][ $k ] = $v;
                    }
                }
            } else {
                //对得到的值 使用函数处理
                foreach ($function as $fk => $fv) {
                    $value              = $fv($value);
                    $processing[ $key ] = $value;
                }
            }
        }
    } else if (is_string($string)) {
        //对得到的值 使用函数处理
        foreach ($function as $key => $value) {
            $processing = $value($string);
        }
    }
    if (!$processing) {
        //是否存在默认值。如果处理后的结果为空，则返回默认值
        $processing = $default === null ? null : $default;
    }

    return $processing;
}

/**
 * 缓存管理
 *
 * @param string $name  存储的名称
 * @param string $value 存储的value
 * @param int    $time  有效期，单位秒
 *
 * @author Colin <15070091894@163.com>
 * @return string
 */
function S($name = '', $value = '', $time = 0) {
    //实例化一个缓存句柄
    $cache = \system\Factory::CreateCache();
    if ($name == 'null') {
        $cache->clear();
    } else if (!empty($name) && is_null($value)) {
        //移除缓存
        $cache->remove($name);
    } else if (!empty($name) && !empty($value)) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        //生成缓存
        $cache->set($name, $value, $time);

        return $value;
    } else if (!empty($name) && empty($value)) {
        //读取缓存
        return json_decode($cache->get($name), true);
    }
}

/**
 * 日志
 *
 * @param string $message 记录内容
 *
 * @author Colin <15070091894@163.com>
 */
function WriteLog($message) {
    \system\IO\File\Log::addRecord($message);
}

/**
 * 系统配置
 *
 * @param string $name  存储的名称
 * @param string $value 存储的value
 *
 * @author Colin <15070091894@163.com>
 * @return string|array
 */
function Config($name = null, $value = '') {
    static $config = [];
    if (empty($name)) {
        return $config;
    } else if (is_array($name)) {
        foreach ($name as $key => &$value) {
            $value = !is_string($value) ? $value : trim($value);
        }
        //设置
        $config = array_merge($config, $name);
    } else if (is_string($name) && $value == '') {
        return isset($config[ $name ]) ? $config[ $name ] : '';
    } else if (is_string($name) && !empty($value)) {
        $config[ $name ] = !is_string($value) ? $value : trim($value);
    }

    return '';
}

/**
 * 获取当前地址
 * @author Colin <15070091894@163.com>
 */
function getCurrentUrl() {
    return \system\Url::getCurrentUrl(true);
}

/**
 * 获取站点地址
 *
 * @param bool $isIndex 是否获取域名
 *
 * @author Colin <15070091894@163.com>
 * @return string
 */
function getSiteUrl($isIndex = false) {
    return \system\Url::getSiteUrl($isIndex);
}

/**
 * 设置Public地址
 *
 * @param string $public public目录的相对地址 可以直接填写Public
 *
 * @author Colin <15070091894@163.com>
 * @return string
 */
function setPublicUrl($public) {
    return getSiteUrl(false) . $public;
}

/**
 * 设置URL地址
 *
 * @param string $url url目录的相对地址
 *
 * @author Colin <15070091894@163.com>
 * @return string
 */
function setUrl($url) {
    return setPublicUrl($url);
}

/**
 * 时间戳格式化
 *
 * @param int    $timestamp 时间戳
 * @param string $model     模式 a 完整的 m 显示到分 h 显示到小时 d 显示到天
 * @param string $mode
 *
 * @return string
 */
function timeFormat($timestamp, $model = 'a', $mode = '') {
    if (!$timestamp) {
        return '-';
    }
    switch ($model) {
        case 'a' :
            $mode = 'Y-m-d H:i:s';
            break;
        case 'm' :
            $mode = 'Y-m-d H:i';
            break;
        case 'h' :
            $mode = 'Y-m-d H';
            break;
        case 'd' :
            $mode = 'Y-m-d';
            break;
    }

    return date($mode, $timestamp);
}

/**
 * 第三方类库调用
 *
 * @param string $name 第三方类库名称
 *
 * @author Colin <15070091894@163.com>
 * @return mixed
 * @throws
 */
function library($name = null) {
    list($filedir, $filename) = explode('/', $name);
    if ($filename == '*') {
        $path = Library . '/' . $filedir;
        $file = \system\Factory::File();
        //获取目录下的所有文件
        $allfile = $file->getDirAllFile($path, 'php');
        //如果目录空 ，则返回null
        if (!$allfile) return;
        foreach ($allfile as $key => $value) {
            //如果是dir,则需要再次遍历，加载
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    require_file($v, null, false);
                }
            } else {
                require_file($value, null, false);
            }
        }
    } else {
        //把@替换成.
        $filename = str_replace('@', '.', $filename);
        $path     = Library . '/' . $filedir . '/' . $filename . '.php';
        if (!file_exists($path)) {
            E('文件不存在' . $name);
        }
        require_file($path);
    }
}

/**
 * 处理Model类的 array_filter 过滤 0 操作
 *
 * @param array $array 过滤的元素
 *
 * @author Colin <15070091894@163.com>
 * @return array
 */
function myclass_filter($array = []) {
    $result = [];
    foreach ($array as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $result[ $key ] = $value;
    }

    return $result;
}

/**
 * 验证表单安全码
 *
 * @param string $secur_number 表单提交的安全码
 *
 * @author Colin <15070091894@163.com>
 * @return bool
 * @throws
 */
function checkSecurity($secur_number = null) {
    if (!$secur_number) {
        return false;
    }
    $system = session('_token');
    if ($secur_number == $system) {
        // session('_token' , 'null');
        return true;
    }

    return false;
}

/**
 * 生成安全密钥文本域
 *
 * @param boolean $token 是否返回token
 *
 * @return string
 */
function _token($token = false) {
    return system\Tool\Form::security($token);
}

/**
 * 获取类名称
 *
 * @param string $name 控制器名
 *
 * @return string
 */
function _getFileName($name) {
    return $name . Config('DEFAULT_CLASS_SUFFIX');
}

/**
 * 设置get参数，Route.php调用
 *
 * @param  [type] $item  [description]
 * @param  [type] $item2 [description]
 */
function maps($item, $item2) {
    if ($item != $item2) {
        $item2          = preg_replace('/[\{|\}]+/', '', $item2);
        $_GET[ $item2 ] = urldecode($item);
    }
}

/**
 * array_walk转换换成html标签属性
 *
 * @param string  $item 属性值
 * @param  string $key  属性名
 */
function walkParams(&$item, $key) {
    $item = $key . '="' . $item . '" ';
}

/**
 * 把数组转换成html标签属性
 *
 * @param array  $attrs         属性值数组
 * @param string $walk_function 回调函数
 *
 * @return string
 */
function walkFormAttr($attrs, $walk_function = 'walkParams') {
    array_walk($attrs, $walk_function);
    $attr = implode('', $attrs);

    return $attr;
}

/**
 * 显示信息
 *
 * @param string $message 信息内容
 *
 * @author Colin <15070091894@163.com>
 */
function ShowMessage($message) {
    header('Content-Type:text/html;charset=UTF-8');
    $info = '<div style="width:400px;height:30%;margin:0 auto;font-size:25px;color:#000;font-weight:bold;">';
    $info .= '<dl style="padding:0px;margin:0px;width:100%;height:100%;border:1px solid #ccc;">';
    $info .= '<dt style="padding:0px;margin:0px;border-bottom:1px solid #ccc;line-height:50px;font-size:20px;text-align:center;background:#efefef;">MyClass提示信息</dt>';
    $info .= '<dd style="padding:0px;width:100%;line-height:25px;font-size:17px;text-align:center;text-indent:0px;margin:0px;padding:30px 0;word-break:break-all;">' . $message . '</dd>';
    $info .= '<dd style="padding:0px;margin:0px;">';
    $info .= '<a href="javascript:void(0);" style="font-size:15px;color:#181884;width:100%;text-align:center;display:block;" id="back">';
    $info .= '[ 返回 ]</a></dd></dd></dl>';
    $info .= '</div><script>';
    $info .= 'var back = document.getElementById("back");
            back.onclick = function(){
                window.history.back();
            }';
    $info .= '</script>';
    die($info);
}

/**
 * 获取IP地址
 * @return array|false|string
 */
function getIp() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP"); else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR"); else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR"); else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR']; else
        $ip = "unknown";

    return ($ip);
}

/**
 * 解析模板文件名
 *
 * @param $filename
 *
 * @return string
 */
function _parseFileName($filename) {
    if (!$filename) {
        $filename = ACTION_NAME;
    }
    // 替换特殊的@符号
    if (strpos($filename, '@') === 0) {
        $route = ltrim(\system\Route\Route::getRoute(), '/');
        $route = explode('/', $route);
        array_pop($route);
        $route = implode('/', $route);
        // @后面没有跟上/，给它加上
        if (strpos($filename, '@/') !== 0) {
            $route .= '/';
        }
        $filename = str_replace('@', $route, $filename);
    }
    $explode = explode('.', $filename);
    if (count($explode) > 1) {
        return $filename;
    }
    $filename .= Config('TPL_TYPE');

    return $filename;
}

/**
 * 扩展smarty的函数
 *
 * @param  [type] $url [description]
 *
 * @return string
 */
function smarty_modifier_url($url) {
    return url($url);
}

/**
 * 预处理函数
 *
 * @param $strInput
 * @param $smarty
 *
 * @return string|string[]|null
 */
function smarty_preFilterConstants($strInput, $smarty) {
    $str = preg_replace("/__(.*)__/", '{$smarty.const.__\\1__}', $strInput);

    return $str;
}

/**
 * 输出常量的值
 *
 * @param      $args
 * @param bool $objSmarty
 *
 * @return mixed
 */
function functionHash($args, $objSmarty = false) {
    $c = $args['c'];
    if (!defined($c)) return constant($c); // 如果常量已定义则抛出异常

    return $c; // 默认的行为
}

/**
 * 解决特殊的sql语法
 *
 * @param string $field
 *
 * @return \system\Model\Select\FieldQuery
 */
function field($field = '') {
    return new \system\Model\Select\FieldQuery($field);
}
