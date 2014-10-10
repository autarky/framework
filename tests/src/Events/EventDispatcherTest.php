<?php
namespace Autarky\Tests\Events;

use Mockery as m;
use PHPUnit_Framework_TestCase;

use Autarky\Events\EventDispatcher;
use Autarky\Events\ListenerResolver;
use Autarky\Container\Container;

class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function mockEvent()
	{
		return m::mock('Symfony\Component\EventDispatcher\Event')
			->makePartial();
	}

	protected function makeDispatcher()
	{
		return new EventDispatcher(new ListenerResolver(new Container));
	}

	/** @test */
	public function simpleDispatchWithString()
	{
		$this->simpleDispatch(__NAMESPACE__.'\StubListener:bar');
	}

	/** @test */
	public function simpleDispatchWithArray()
	{
		$this->simpleDispatch([__NAMESPACE__.'\StubListener', 'bar']);
	}

	protected function simpleDispatch($listener)
	{
		$events = $this->makeDispatcher();
		$events->addListener('foo', $listener);
		$mockEvent = $this->mockEvent();
		$mockEvent->shouldReceive('doStuff')->once();
		$event = $events->dispatch('foo', $mockEvent);
		$this->assertSame($event, $mockEvent);
	}
}

class StubListener
{
	public function __construct(StubDependency $dep)
	{
		$this->dep = $dep;
	}

	public function bar($event)
	{
		$this->dep->doStuff($event);
		return $event;
	}
}

class StubDependency
{
	public function doStuff($event)
	{
		$event->doStuff();
	}
}
