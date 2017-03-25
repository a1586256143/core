<?php
/**
 * 中间件
 * @author Colin <15070091894@163.com>
 */
namespace system\Route;
use system\Base;
abstract class Middleware extends Base{

	/**
	 * 系统初始化
	 * @author Colin <15070091894@163.com>
	 * @return [type] [description]
	 */
	public function __construct(){
		self::init();
	}

	/**
	 * 初始化
	 * @author Colin <15070091894@163.com>
	 * @return [type] [description]
	 */
	public static function init(){
		
	}

	/**
	 * 执行中间件
	 * @author Colin <15070091894@163.com>
	 * @return [type] [description]
	 */
	abstract function execMiddleware();
}