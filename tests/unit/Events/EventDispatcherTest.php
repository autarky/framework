<?php

use Mockery as m;

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

	protected function dispatchMockEvent($dispatcher, $method = 'doStuff')
	{
		$mockEvent = $this->mockEvent();
		$mockEvent->shouldReceive($method)->once();
		$event = $dispatcher->dispatch('foo', $mockEvent);
		$this->assertSame($event, $mockEvent);
	}

	/** @test */
	public function listenersAreResolved()
	{
		$resolver = m::mock('Autarky\Events\ListenerResolver');
		$dispatcher = new EventDispatcher($resolver);
		$dispatcher->addListener('foo', 'StubListener');
		$resolver->shouldReceive('resolve')->with('StubListener')->once()->andReturn(new StubListener);
		$this->dispatchMockEvent($dispatcher, 'doOtherStuff');
	}

	/** @test */
	public function listenerIsResolvedAndCorrectMethodIsCalled()
	{
		$resolver = m::mock('Autarky\Events\ListenerResolver');
		$dispatcher = new EventDispatcher($resolver);
		$dispatcher->addListener('foo', ['StubListener', 'bar']);
		$resolver->shouldReceive('resolve')->with('StubListener')->once()->andReturn(new StubListener);
		$this->dispatchMockEvent($dispatcher);
	}

	/** @test */
	public function closureListenersStillWork()
	{
		$resolver = m::mock('Autarky\Events\ListenerResolver');
		$dispatcher = new EventDispatcher($resolver);
		$dispatcher->addListener('foo', function($event) { $event->doStuff(); });
		$this->dispatchMockEvent($dispatcher);
	}

	/** @test */
	public function callableArrayListenersStillWork()
	{
		$resolver = m::mock('Autarky\Events\ListenerResolver');
		$dispatcher = new EventDispatcher($resolver);
		$dispatcher->addListener('foo', [new StubListener, 'bar']);
		$this->dispatchMockEvent($dispatcher);
	}
}

class StubListener
{
	public function handle($event)
	{
		$event->doOtherStuff();
	}
	public function bar($event)
	{
		$event->doStuff();
	}
}

class StubEvent extends \Symfony\Component\EventDispatcher\Event
{
	public function doStuff() {}
	public function doOtherStuff() {}
}
