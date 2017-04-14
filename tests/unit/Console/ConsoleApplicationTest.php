<?php

use Mockery as m;

class ConsoleApplicationTest extends PHPUnit\Framework\TestCase
{
	/** @test */
	public function autarkyApplicationInstanceIsSetOnCommands()
	{
		$capp = new \Autarky\Console\Application;
		$capp->setAutarkyApplication($app = new \Autarky\Application('testing', []));
		$cmd = m::mock('Autarky\Console\Command[setAutarkyApplication]', ['testcmd']);
		$cmd->shouldReceive('setAutarkyApplication')->with($app)->once();
		$capp->add($cmd);
		m::close();
	}
}
