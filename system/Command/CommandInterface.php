<?php
namespace system\Command;
interface CommandInterface{
	public function exec();

	public function generate();
}