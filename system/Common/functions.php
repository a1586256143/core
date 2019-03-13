<?php
/*
	Author : Colin,
	Creation time : 2015-8-1
	FileType : 函数库
	FileName : function.php
*/

/**
 * 返回json数据
 * @param array 要返回的数据
 * @author Colin <15070091894@163.com>
 */
function ajaxReturn($array = null){
	echo json_encode($array);
	exit;
}

/**
 * M方法实例化模型
 * @param name 模型名称
 * @author Colin <15070091894@163.com>
 */
function M($name = null){
	if(empty($name)){
		//创建系统模型   不带表名
		return system\ObjFactory::CreateSystemModel();
	}else {
		//创建系统模型   带表名
		return system\ObjFactory::CreateSystemModel($name);
	}
}

/**
 * E方法对错误进行提醒
 * @param message 地址
 * @author Colin <15070091894@163.com>
 */
function E($message){
	$debug = Debug;
	//记录日志
	WriteLog($message);
	if($debug){
		throw new \system\MyError($message);
	}else{
		throw new \system\MyError(Config('ERROR_MESSAGE'));
	}
}

/**
 * 引入常规文件    没有返回值
 * @param path 文件路径
 * @param modules 加载的模块
 * @author Colin <15070091894@163.com>
 */
function require_file($path , $modules = '' , $return = true){
	$content = [];
	if(is_array($path)){
		foreach ($path as $key => $value) {
			if(file_exists($modules.$value)){
				$content[] = require_once $modules.$value;
			}
		}
		return $content;
	}else if(is_string($path)){
		if(file_exists($path)){
			if($return){
				return require_once $path;
			}else{
				require_once $path;
			}
			
		}
	}
}

/**
 * 合并配置值
 * @param name1 第一个需合并的数组
 * @param name2 第二个需合并的数组
 * @param name3 第三个需合并的数组
 * @author Colin <15070091894@163.com>
 */
function replace_recursive_params($name1 , $name2 = null , $name3 = null){
	$var1 = require_file($name1);
	$var2 = require_file($name2);
	$var3 = require_file($name3);
	if(empty($var2) && empty($var3)){
		return $var1;
	}else if(empty($var3)){
		return array_replace_recursive($var1 , $var2);
	}else if(!empty($var3)){
		$merge = array_replace_recursive($var1 , $var3);
		return array_replace_recursive($merge , $var2);
	}else{
		return $var1;
	}
}

/**
 * 创建文件夹 支持批量创建
 * @param param 文件夹数组
 * @author Colin <15070091894@163.com>
 */
function outdir($param){
	$result = array();
	if(is_array($param)){
		foreach ($param as $key => $value) {
			if(!is_dir($value)){
				$result[$value] = mkdir($value , 0777);
			}
		}
	}else if(is_string($param)){
		if(!is_dir($param)) $result[$param] = mkdir($param , 0777);
	}
	return $result;
}

/**
 * 打印输出函数
 * @param array 要被打印的数据
 * @author Colin <15070091894@163.com>
 */
function dump($array){
	header('Content-type:text/html;charset="UTF-8"');
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

/**
 * 生成url
 * @return [type] [description]
 */
function url($url){
	return getSiteUrl(true) . '/' . ltrim($url , '/');
}

/**
 * 设置session
 * @param name session的名称
 * @param value session要保存的值
 * @author Colin <15070091894@163.com>
 */
function session($name = '' , $value = ''){
	if(Config('SESSION_START')){
		//session名称为空 返回所有
		if($name === ''){
			return $_SESSION;
		}else if($name == 'null'){					//清空session
			return session_destroy();
		}else if(!empty($name) && $value === ''){	//session值为空
			return $_SESSION["$name"] !== 'null' ? $_SESSION["$name"] : null;
		}else if(!empty($name) && !empty($value)){	//session名称和值都不为空
			$_SESSION["$name"] = $value;
		}else if(!empty($name) && is_null($value)){
			unset($_SESSION["$name"]);
		}
	}else{
		throw new \system\MyError('session未打开！请进入config.php打开');
	}
}

/**
 * 接收post和get值函数
 * @param type 要获取的POST或GET
 * @param formname 要获取的POST或type的表单名
 * @param function 要使用的函数
 * @param default 默认值
 * @author Colin <15070091894@163.com>
 */
function values($type , $formname = null , $function = 'trim' , $default = null){
	switch ($type) {
		case 'get':
			$string = isset($_GET[$formname]) ? $_GET[$formname] : '';
			break;
		case 'get.':
			$string = $_GET;
			break;
		case 'post':
			$string = isset($_POST[$formname]) ? $_POST[$formname] : '';
			break;
		case 'post.':
			$string = $_POST;
			break;
		case 'files':
			$string = isset($_FILES[$formname]) ? $_FILES[$formname] : '';
			break;
		case 'files.':
			$string = $_FILES;
			break;
		case 'request':
			$string = $_REQUEST[$formname];
			break;
	}
	if($function == null){
		return $string;
	}
	//解析函数，得到函数名
	$function = explode(',', $function);
	$processing = '';
	if(is_array($string)){
		foreach ($string as $key => $value) {
			if(is_array($value)){
				foreach ($value as $k => $v) {
					//对得到的值 使用函数处理
					foreach ($function as $fk => $fv) {
						$v = $fv($v);
						$processing[$key][$k] = $v;
					}
				}
			}else{
				//对得到的值 使用函数处理
				foreach ($function as $fk => $fv) {
					$value = $fv($value);
					$processing[$key] = $value;
				}
			}
			
			
		}
	}else if(is_string($string)){
		//对得到的值 使用函数处理
		foreach ($function as $key => $value) {
			$processing = $value($string);
		}
	}
	if(!$processing){
		//是否存在默认值。如果处理后的结果为空，则返回默认值
		$processing = $default === null ? null : $default;
	}
	return $processing;
}

/**
 * 缓存管理
 * @param name 存储的名称
 * @param value 存储的value
 * @author Colin <15070091894@163.com>
 */
function S($name = '' , $value = '' , $time = 0){
	//实例化一个缓存句柄
	$cache = \system\ObjFactory::CreateCache();
	if($name == 'null'){
		$cache->clearCache();
	}else if(!empty($name) && is_null($value)){
		//移除缓存
		$cache->removeCache($name);
	}else if(!empty($name) && !empty($value)){
		//生成缓存
		$cache->outputFileName($name , $value);
		return $value;
	}else if(!empty($name) && empty($value)){
		//读取缓存
		return $time?$cache->readCache($name,$time):$cache->readCache($name);
	}
}

/**
 * 日志
 * @author Colin <15070091894@163.com>
 */
function WriteLog($message , $logConfigName = 'LOG_NAME_FORMAT'){
	$logDir = Config('LOGDIR');
	//创建日志文件夹
	outdir($logDir);
	//日志文件名格式
	$logName = Config($logConfigName);
	$logName = date($logName , time());
	//日志后缀
	$logSuffix = Config('LOG_SUFFIX');
	$file = new \system\File();
	$url = getCurrentUrl();
	$logPath = $logDir . '/' . $logName . $logSuffix;
	$data = date('[ Y-m-d H:i:s ]') . " $url\r\n$message\r\n";
	$status = $file->AppendFile($logPath , $data , false);
}

/**
 * 系统配置
 * @param name 存储的名称
 * @param value 存储的value
 * @author Colin <15070091894@163.com>
 */
function Config($name = null , $value = ''){
	static $config = array();
	if(empty($name)){
		return $config;
	}else if(is_array($name)){
		//设置
		$config = array_merge($config , $name);
	}else if(is_string($name) && $value == ''){
		return $config[$name];
	}else if(is_string($name) && !empty($value)){
		$config[$name] = $value;
	}
}

/**
 * 获取当前地址
 * @author Colin <15070091894@163.com>
 */
function getCurrentUrl(){
	return \system\Url::getCurrentUrl(true);
}

/**
 * 获取站点地址
 * @author Colin <15070091894@163.com>
 */
function getSiteUrl($isIndex = false){
	return \system\Url::getSiteUrl($isIndex);
}


/**
 * 设置Public地址
 * @param  public public目录的相对地址 可以直接填写Public
 * @author Colin <15070091894@163.com>
 */
function setPublicUrl($public){
	return getSiteUrl(false).$public;
}

/**
 * 设置URL地址
 * @param  url url目录的相对地址
 * @author Colin <15070091894@163.com>
 */
function setUrl($url){
	return setPublicUrl($url);
}

/**
 * 获取当前时间
 * @param prc 时间区域
 * @author Colin <15070091894@163.com>
 */
function getTime($prc = null){
	return \system\Date::getDate($prc);
}

/**
 * 第三方类库调用
 * @param name 第三方类库名称
 * @author Colin <15070091894@163.com>
 */
function library($name = null){
	list($filedir , $filename) = explode('/' , $name);
	if($filename == '*'){
		$path = Library . '/' . $filedir;
		$file = new \system\File();
		//获取目录下的所有文件
		$allfile = $file->getDirAllFile($path , 'php');
		//如果目录空 ，则返回null
		if(!$allfile) return;
		foreach($allfile as $key => $value){
			//如果是dir,则需要再次遍历，加载
			if(is_array($value)){
				foreach ($value as $k => $v) {
					require_file($v , null , false);
				}
			}else{
				require_file($value , null , false);
			}
		}
	}else{
		//把@替换成.
		$filename = str_replace('@' , '.' , $filename);
		$path = Library.'/'.$filedir.'/'.$filename.'.php';
		if(!file_exists($path)){
			E('文件不存在'.$name);	
		}
		require_file($path);
	}
}

/**
 * 处理Model类的 array_filter 过滤 0 操作
 * @author Colin <15070091894@163.com>
 */
function myclass_filter($array = array()){
	foreach ($array as $key => $value) {
		if($value === null || $value === ''){
			continue;
		}
		$result[$key] = $value;
	}
	return $result;
}

/**
 * 验证表单安全码
 * @param string $secur_number 表单提交的安全码
 * @author Colin <15070091894@163.com>
 */
function checkSecurity($secur_number = null){
	if(!$secur_number){
		return false;
	}
	$system = session('_token');
	if($secur_number == $system){
		// session('_token' , 'null');
		return true;
	}
	return false;
}

/**
 * 生成安全密钥文本域
 * @param boolean $token 是否返回token
 * @return [type] [description]
 */
function _token($token = false){
	return system\Form::security($token);
}

/**
 * 获取类名称
 * @return [type] [description]
 */
function get_filename($name){
	return $name . Config('DEFAULT_CLASS_SUFFIX');
}

/**
 * 设置get参数，Route.class.php调用
 * @param  [type] $item  [description]
 * @param  [type] $item2 [description]
 * @return [type]        [description]
 */
function maps($item , $item2){
	if($item != $item2){
		$item2 = preg_replace('/[\{|\}]+/' , '' , $item2);
		$_GET[$item2] = urldecode($item);
	}
}

/**
 * array_walk转换换成html标签属性
 * @return [type] [description]
 */
function walkParams(&$item , $key , $value){
	$item = $key . '="' . $item . '" ';
}

/**
 * 把数组转换成html标签属性
 * @param  [type] $attrs         属性值数组
 * @param  [type] $walk_function 回掉函数
 * @return [type]                [description]
 */
function walkFormAttr($attrs , $walk_function = 'walkParams'){
	array_walk($attrs , $walk_function);
	$attr = implode('' , $attrs);
	return $attr;
}

/**
 * 显示信息
 * @param message 信息内容
 * @author Colin <15070091894@163.com>
 */
function ShowMessage($message){
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
?>