<?php
namespace Autarky\Tests\Events;

use Autarky\Tests\TestCase;
use Mockery as m;

class ServiceProviderTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication('Autarky\Events\EventDispatcherProvider');
		$app->boot();
		$object = $app->getContainer()->resolve('Autarky\Events\EventDispatcher');
		$this->assertInstanceOf('Autarky\Events\EventDispatcher', $object);
		$this->assertSame($object, $app->getContainer()->resolve('Autarky\Events\EventDispatcher'));
	}

	/** @test */
	public function canResolveFromInterface()
	{
		$app = $this->makeApplication('Autarky\Events\EventDispatcherProvider');
		$app->boot();
		$object = $app->resolve('Symfony\Component\EventDispatcher\EventDispatcherInterface');
		$this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcherInterface', $object);
	}

	/** @test */
	public function setsEventDispatcherOnDispatcherAwareInterface()
	{
		$app = $this->makeApplication('Autarky\Events\EventDispatcherProvider');
		$app->boot();
		$mock = m::mock('Autarky\Events\EventDispatcherAwareInterface');
		$app->getContainer()->share('foo', function() use ($mock) { return $mock; });
		$mock->shouldReceive('setEventDispatcher')->once();
		$app->resolve('foo');
	}
}
