<?php

use Mockery as m;

class ConsoleApplicationTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function autarkyApplicationInstanceIsSetOnCommands()
	{
		$capp = new \Autarky\Console\Application;
		$capp->setAutarkyApplication($app = new \Autarky\Kernel\Application('testing', []));
		$cmd = m::mock('Autarky\Console\Command[setAutarkyApplication]', ['testcmd']);
		$cmd->shouldReceive('setAutarkyApplication')->with($app)->once();
		$capp->add($cmd);
		m::close();
	}
}
