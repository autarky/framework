<?php
namespace Autarky\Tests\Kernel;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Autarky\Kernel\ErrorHandler;
use Exception;

class ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
	protected function makeHandler()
	{
		return new ErrorHandler(true, false);
	}

	/** @test */
	public function prependedHandlersGetPriority()
	{
		$handler = $this->makeHandler();
		$handler->appendHandler(function(Exception $e) { return 'foo'; });
		$handler->prependHandler(function(Exception $e) { return 'bar'; });
		$this->assertEquals('bar', $handler->handle(new Exception));
	}

	/** @test */
	public function wrongTypehintHandlersAreNotCalled()
	{
		$handler = $this->makeHandler();
		$handler->appendHandler(function(Exception $e) { return 'foo'; });
		$handler->prependHandler(function(\RuntimeException $e) { return 'bar'; });
		$this->assertEquals('foo', $handler->handle(new \InvalidArgumentException));
	}
}
