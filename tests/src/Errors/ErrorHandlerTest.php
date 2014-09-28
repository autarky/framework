<?php
namespace Autarky\Tests\Errors;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Exception;

use Autarky\Errors\ErrorHandlerManager;

class ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function makeHandler()
	{
		return new ErrorHandlerManager(m::mock('Autarky\Kernel\Application'), true, false);
	}

	/** @test */
	public function prependedHandlersGetPriority()
	{
		$handler = $this->makeHandler();
		$handler->appendHandler(function(Exception $e) { return 'foo'; });
		$handler->prependHandler(function(Exception $e) { return 'bar'; });
		$result = $handler->handle(new Exception);
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
		$this->assertEquals('bar', $result->getContent());
		$this->assertEquals(500, $result->getStatusCode());
	}

	/** @test */
	public function wrongTypehintHandlersAreNotCalled()
	{
		$handler = $this->makeHandler();
		$handler->appendHandler(function(Exception $e) { return 'foo'; });
		$handler->prependHandler(function(\RuntimeException $e) { return 'bar'; });
		$result = $handler->handle(new \InvalidArgumentException);
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
		$this->assertEquals('foo', $result->getContent());
		$this->assertEquals(500, $result->getStatusCode());
	}
}
