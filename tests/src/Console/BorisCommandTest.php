<?php
namespace Autarky\Tests\Console;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BorisCommandTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		// ...
	}

	public function tearDown()
	{
		// ...
	}

	protected function makeCommand($app)
	{
		$cmd = new \Autarky\Console\BorisCommand;
		$cmd->setAutarkyApplication($app);
		return $cmd;
	}

	/** @test */
	public function executeCommandThrowsExceptionIfClassDoesNotExist()
	{
		if (class_exists('Boris\Boris')) {
			$this->markTestSkipped('Class Boris\Boris must not exist for this test to work');
		}

		$app = m::mock('Autarky\Kernel\Application');
		$cmd = $this->makeCommand($app);
		$this->setExpectedException('RuntimeException', 'Install d11wtq/boris via composer to use this command.');
		$cmd->run(new ArrayInput([]), new BufferedOutput);
	}

	/** @test */
	public function executeCommandInvokesBoris()
	{
		$classExists = class_exists('Boris\Boris');

		$app = m::mock('Autarky\Kernel\Application');
		$cmd = $this->makeCommand($app);
		$app->shouldReceive('getErrorHandler')->andReturn($ehm = m::mock('Autarky\Errors\ErrorHandlerManagerInterface'));
		$ehm->shouldReceive('prependHandler')->with(m::type('Closure'))->once();
		$boris = m::mock('Boris\Boris');
		if (!$classExists) {
			$boris->shouldAllowMockingMethod('setLocal');
			$boris->shouldAllowMockingMethod('start');
		}
		$boris->shouldReceive('setLocal')->with(['app' => $app])->once();
		$boris->shouldReceive('start')->once();
		$cmd->setBoris($boris);
		$cmd->run(new ArrayInput([]), new BufferedOutput);
	}
}
