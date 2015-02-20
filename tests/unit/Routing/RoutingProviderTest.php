<?php

use Autarky\Tests\TestCase;
use Mockery as m;

class RoutingProviderTest extends TestCase
{
	protected function checkResolve($class)
	{
		$app = $this->makeApplication([
			'Autarky\Events\EventDispatcherProvider',
			'Autarky\Routing\RoutingProvider'
		]);
		$app->boot();
		$object = $app->getContainer()->resolve($class);
		$this->assertInstanceOf($class, $object);
		$this->assertSame($object, $app->getContainer()->resolve($class));
	}

	/** @test */
	public function canResolveRouter()
	{
		$this->checkResolve('Autarky\Routing\Router');
	}

	/** @test */
	public function canResolveRouterInterface()
	{
		$this->checkResolve('Autarky\Routing\Router');
	}

	/** @test */
	public function canResolveUrlGenerator()
	{
		$this->checkResolve('Autarky\Routing\Router');
	}

	/** @test */
	public function canResolveRequestStack()
	{
		$app = $this->makeApplication('Autarky\Routing\RoutingProvider');
		$app->boot();
		$this->assertSame($app->getRequestStack(), $app->getContainer()->resolve('Symfony\Component\HttpFoundation\RequestStack'));
	}
}
