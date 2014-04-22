<?php
namespace Autarky\Tests\Events;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Autarky\Events\EventDispatcher;
use Autarky\Container\IlluminateContainer;

class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
	protected function mockEvent()
	{
		return m::mock('Symfony\Component\EventDispatcher\Event')
			->makePartial();
	}

	protected function makeDispatcher()
	{
		return new EventDispatcher(new IlluminateContainer);
	}

	/** @test */
	public function simpleDispatch()
	{
		$events = $this->makeDispatcher();
		$events->addListener('foo', __NAMESPACE__.'\StubListener:bar');
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
