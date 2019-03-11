<?php
/**
 * URL处理
 * @author Colin <15070091894@163.com>
 */
namespace system;
class Url{
    public static $param = array();

    /**
     * 解析URL，得到url后面的参数
     * @param string $url 被解析的地址，转换成路由，为空，则默认当前
     * @return [type] [description]
     */
    public static function parseUrl($url = null){
        $url = $url == '' ?  '//' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : $url;
        $path = parse_url($url);
        $if_path = $path['path'] ? ltrim($path['path'] , '/') : '';
        $parse_path = explode('/' , $if_path);
        $path_array = explode('/' , ROOT_PATH);
        $patten = '/\./';
        foreach ($parse_path as $key => $value) {
            //处理根目录
            if(in_array($value , $path_array)){
                unset($parse_path[$key]);
            }
            $parse_path = array_merge($parse_path);
        }
        $if_path = implode('/' , $parse_path);
        //解析.
        if(preg_match('/([\w]+\.php)([\/\w\_\{\}]+)/' , $if_path , $match)){
            //去除空值和重新合并数组
            $url_merge = array_merge(array_filter(explode($match[1] , $if_path)));
            if(!$url_merge[0]){
                return '/';
            }
            return urldecode($url_merge[0]);
        }else if($if_path){
            //解决没有index.php时，出现无法加载问题
            return '/' . $if_path;
        }
        return '/';
    }

    /**
     * 获取当前url
     * @param boolean $is_return_current_url 是否返回当前地址
     * @param boolean $is_return_array       是否返回数组
     * @return array
     * @author Colin <15070091894@163.com>
     */
    public static function getCurrentUrl($is_return_current_url = false , $is_return_array = false){
        $current_url = self::getSiteUrl(true , true);
        $parse_url = parse_url($current_url);
        $path_array = explode('/' , ROOT_PATH);
        $patten = '/\./';
        foreach ($parse_url as $key => $value) {
            //处理根目录
            if(in_array($value , $path_array)){
                unset($parse_url[$key]);
            }
            $parse_url = array_merge($parse_url);
        }
        //匹配是否是index.php
        if(!empty($parse_url) && isset($parse_url[0])){
            if(preg_match($patten,$parse_url[0],$match)){
                //确认文件是否存在
                if(!file_exists(ROOT_PATH.$parse_url[0])){
                    E('Invalid entry file '.$parse_url[0]);
                }
                unset($parse_url[0]);
            }
            $parse_url = array_merge($parse_url);
        }
        if(empty($parse_url)){
            $parse_url = array(Config('DEFAULT_MODULE') , Config('DEFAULT_CONTROLLER') , Config('DEFAULT_METHOD'));
        }
        self::$param = $parse_url;
        if($is_return_current_url) return $current_url;
        if($is_return_array) return $parse_url;
        return $parse_url;
    }

    /**
     * 获取域名
     * @param boolean $scheme 是否返回域名
     * @param boolean $param 是否返回地址栏输入的参数
     * @return array
     * @author Colin <15070091894@163.com>
     */
    public static function getSiteUrl($scheme = false , $param = false){
        $hostName = $_SERVER['HTTP_HOST'];
        $params = $_SERVER['REQUEST_URI'];
        $scheme = $_SERVER['REQUEST_SCHEME'] . '://';
        if($scheme && !$param){
            return  $scheme . $hostName;
        }
        if($scheme && $param){
            return $scheme . $hostName . $params;
        }
        if(!$scheme && $param){
            return $hostName . $params;
        }
        return $hostName;
    }
}
?>