<?php
namespace system\Command;
use system\Factory;
use system\Facade;
class Command extends Facade{

	protected static $container;

	protected static $argv;

	/**
	 * 初始化
	 * @param [type] $argv [description]
	 */
	public function __construct($argv){
		self::$container = Factory::Container();
		$this->run();
		self::$argv = $argv;
	}

	/**
	 * 获取门面名
	 * @return [type] [description]
	 */
	public static function getFacadeAccessor(){
        return self::$container->make(self::$argv[1] , self::$argv);
    }

	/**
	 * 运行方法
	 * @return [type] [description]
	 */
	public function run(){
		self::$container->bind('controller' , function(){
			return ControllerCommand::class;
		});
		self::$container->bind('model' , function(){
			return ModelCommand::class;
		});
	}
}