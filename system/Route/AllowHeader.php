<?php
namespace system\Route;

class AllowHeader {
	protected static $config = [];
	/**
	 * 设置请求头
	 * @author Colin
	 * @date 2021-04-23 下午2:06
	 */
	public static function enable(){
		self::$config = envg('allowDefault');
		$config = self::$config['Origin'] ? self::$config['Origin'] : [];
		$host = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
		if (is_array($config) && count($config) > 0){
			if (!in_array($host , $config)){
				return;
			}
		}
		self::setHeaderOrigin($host);
		self::setRequestHeader();
		self::setCredentials();
	}

	/**
	 * 设置运行的域名
	 * @param string $host
	 * @author Colin
	 * @date 2021-04-23 下午2:23
	 */
	protected static function setHeaderOrigin($host = ''){
		header('Access-Control-Allow-Origin:' . $host);
	}

	/**
	 * 设置携带cookie
	 * @param string $status
	 * @author Colin <amcolin@126.com>
	 * @date 2021-12-22 下午2:41
	 */
	protected static function setCredentials($status = 'true'){
		header('Access-Control-Allow-Credentials:' . $status);
	}

	/**
	 * 设置请求的字段头
	 * @author Colin <amcolin@126.com>
	 * @date 2021-12-22 下午3:34
	 */
	protected static function setRequestHeader(){
		$headers = implode(',' , self::$config['Headers']);
		if ($headers){
			$headers = ',' . $headers;
		}
		header('Access-Control-Allow-Headers: Accept,Content-Type,x-requested-with' . $headers);
	}
}