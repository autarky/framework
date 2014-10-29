<?php
namespace Autarky\Tests\Events;

use Mockery as m;
use PHPUnit_Framework_TestCase;

use Autarky\Events\EventDispatcher;

class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function mockEvent()
	{
		return m::mock(__NAMESPACE__.'\StubEvent')
			->makePartial();
	}

	protected function makeDispatcher()
	{
		return new EventDispatcher(new ListenerResolver(new Container));
	}

	/** @test */
	public function listenersAreResolved()
	{
		$resolver = m::mock('Autarky\Events\ListenerResolver');
		$events = new EventDispatcher($resolver);
		$resolver->shouldReceive('resolve')->with('class')->once()->andReturn(new StubListener);
		$events->addListener('foo', ['class', 'bar']);
		$mockEvent = $this->mockEvent();
		$mockEvent->shouldReceive('doStuff')->once();
		$event = $events->dispatch('foo', $mockEvent);
		$this->assertSame($event, $mockEvent);
	}
}

class StubListener
{
	public function bar($event)
	{
		$event->doStuff();
		return $event;
	}
}

class StubEvent extends \Symfony\Component\EventDispatcher\Event
{
	public function doStuff() {}
}
