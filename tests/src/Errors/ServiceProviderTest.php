<?php
namespace Autarky\Tests\Errors;

use Autarky\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication('Autarky\Errors\ErrorHandlerProvider');
		$app->boot();
		$object = $app->getContainer()->resolve('Autarky\Errors\ErrorHandlerManager');
		$this->assertInstanceOf('Autarky\Errors\ErrorHandlerManager', $object);
		$this->assertSame($object, $app->getContainer()->resolve('Autarky\Errors\ErrorHandlerManager'));
	}

	/** @test */
	public function canResolveFromInterface()
	{
		$app = $this->makeApplication('Autarky\Errors\ErrorHandlerProvider');
		$app->boot();
		$object = $app->getContainer()->resolve('Autarky\Errors\ErrorHandlerManagerInterface');
		$this->assertInstanceOf('Autarky\Errors\ErrorHandlerManagerInterface', $object);
	}
}
