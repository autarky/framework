<?php
namespace Autarky\Tests\Console;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class ApplicationTest extends PHPUnit_Framework_TestCase
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
