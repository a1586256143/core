<?php
namespace system\Command;
class ControllerCommand implements CommandInterface{
	public function __construct($argv = null){
		dump($argv);
	}

	public function exec(){
		return self::class;
	}

	public function generate(){

	}
}