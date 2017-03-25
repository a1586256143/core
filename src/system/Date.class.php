<?php
/**
 * 时间处理
 * @author Colin <15070091894@163.com>
 */
namespace system;
class Date{
	/**
	 * 设置默认时区
	 * @param string $timezone 设置时区，默认上海
	 * @author Colin <15070091894@163.com>
	 */
	public static function set_timezone($timezone = 'Asia/Shanghai'){
		date_default_timezone_set($timezone);
	}

	/**
	 * 获取时间
	 * @param timezone 时间区域
	 * @return int
	 * @author Colin <15070091894@163.com>
	 */
	public static function getDate($timezone = null){
		if($timezone == null){
			date_default_timezone_set(Config('DATE_DEFAULT_TIMEZONE'));
		}else{
			date_default_timezone_set($timezone);
		}
		return time();
	}
	
	/**
	 * 设置时间格式
	 * @param model 时间格式
	 * @param timestamp 时间戳
	 * @return string
	 * @author Colin <15070091894@163.com>
	 */
	public static function setDate($model , $timestamp){
		date_default_timezone_set(Config('DATE_DEFAULT_TIMEZONE'));
		if($model == null){
			return date('Y-m-d H:i:s' , $timestamp);
		}elseif($timestamp == null){
			return date($model , time());
		}elseif($model == null && $timestamp != null){
			return date('Y-m-d H:i:s' , $timestamp);
		}elseif($model != null && $timestamp == null){
			return date($model , time());
		}elseif($model != null && $timestamp != null){
			return date($model , $timestamp);
		}
	}
}