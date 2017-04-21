<?php
/**
 * 缓存
 * @author Colin <15070091894@163.com>
 */
namespace system;
class Cache {
	/**
	 * 初始化
	 * @author Colin <15070091894@163.com>
	 */
	public function __construct(){
		$this->file = new File();
		$this->cache_out_suffix = Config('CACHE_OUT_SUFFIX');
		$this->cache_out_prefix = Config('CACHE_OUT_PREFIX');
		$this->cache_data_dir = Config('CACHE_DATA_DIR');
		//检查目录的状态和权限
		$this->CacheDir();
	}

	/**
	 * 验证缓存目录是否存在
	 * @param name 缓存名
	 * @param data 存储数据
	 * @author Colin <15070091894@163.com>
	 */
	public function CacheDir(){
		//检查是否有可写的权限
		if(!is_writable($this->cache_data_dir)){
			E('该目录没有可写权限！' . $this->cache_data_dir);
		}
		//检查是否有可读的全选
		if(!is_readable($this->cache_data_dir)){
			E('该目录没有可读权限！' . $this->cache_data_dir);
		}
	}

	/**
	 * 输出文件名
	 * @param name 缓存名
	 * @param data 存储数据
	 * @author Colin <15070091894@163.com>
	 */
	public function outputFileName($name , $data){
		//组合地址
		$FileName = $this->UrlAndDefaultSuffix($name);
		//写入文件
		if(!$this->file->WriteFile($FileName , $data)){
			E('写入文件失败！' . $FileName);
		}
	}

	/**
	 * 生成缓存默认前缀
	 * @param name 缓存名
	 * @param data 存储数据
	 * @author Colin <15070091894@163.com>
	 */
	public function cache_out_prefix(){
		if(empty($this->cache_out_prefix)){
			$this->cache_out_prefix = substr(date('Y') , 2 , 2) . '_';
		}
	}

	/**
	 * 组装地址以及默认前缀
	 * @param name 缓存名
	 * @author Colin <15070091894@163.com>
	 */
	public function UrlAndDefaultSuffix($name){
		//生成文件名默认前缀
		$this->cache_out_prefix();
		//生成文件名
		$FileName = $this->cache_out_prefix . md5($this->cache_out_prefix . $name) . $this->cache_out_suffix;
		//组合地址
		$FileName = $this->cache_data_dir . $FileName;
		return $FileName;
	}

	/**
	 * 读取缓存
	 * @param name 缓存名
	 * @param data 存储数据
	 * @author Colin <15070091894@163.com>
	 */
	public function readCache($name,$time=0){
		$FileName = $this->UrlAndDefaultSuffix($name);
		$json = $time ? $this->file->OpenFile($FileName , $time) : $this->file->OpenFile($FileName);
		return json_decode($json , true);
	}

	/**
	 * 移除缓存
	 * @param name 缓存名
	 * @author Colin <15070091894@163.com>
	 */
	public function removeCache($name){
		$FileName = $this->UrlAndDefaultSuffix($name);
		//开始删除文件
		$this->file->DeleteFile($FileName);
	}

	/**
	 * 清楚全部缓存
	 * @author Colin <15070091894@163.com>
	 */
	public function clearCache(){
		$this->file->ClearPathData($this->cache_data_dir);
	}
}