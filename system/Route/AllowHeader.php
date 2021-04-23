<?php
namespace system\Route;

class AllowHeader {
	/**
	 * 设置请求头
	 * @author Colin
	 * @date 2021-04-23 下午2:06
	 */
	public static function enable(){
		$config = envg('allowDefault');
		$config = $config['Origin'] ? $config['Origin'] : [];
		$host = '*';
		if (is_array($config) && count($config) > 0){
			$host = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
			if (in_array($host , $config)){
				return;
			}
		}
		self::setHeaderOrigin($host);
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
}