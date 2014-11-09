<?php

use Mockery as m;

use Autarky\Kernel\HttpKernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class HttpKernelTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeKernel($events = true)
	{
		$this->router = m::mock('Autarky\Routing\RouterInterface');
		$this->errorHandler = m::mock('Autarky\Errors\ErrorHandlerInterface');
		$this->requests = new \Symfony\Component\HttpFoundation\RequestStack;
		$this->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher;
		return new HttpKernel($this->router, $this->errorHandler, $this->requests, $events ? $this->eventDispatcher : null);
	}

	/** @test */
	public function basicHandle()
	{
		$kernel = $this->makeKernel();
		$this->router->shouldReceive('dispatch')->once()->andReturn($response = new Response('foo'));
		$this->assertSame($response, $kernel->handle(Request::create('/')));
	}

	/** @test */
	public function requestListenerCanReturnResponse()
	{
		$kernel = $this->makeKernel();
		$this->eventDispatcher->addListener('kernel.request', function($event) {
			$event->setResponse(new Response('event response'));
		});
		$response = $kernel->handle(Request::create('/'));
		$this->assertEquals('event response', $response->getContent());
	}

	/** @test */
	public function responseListenerCanOverwriteResponse()
	{
		$kernel = $this->makeKernel();
		$this->router->shouldReceive('dispatch')->once()->andReturn(new Response('router response'));
		$this->eventDispatcher->addListener('kernel.response', function($event) {
			$event->setResponse(new Response('event response'));
		});
		$response = $kernel->handle(Request::create('/'));
		$this->assertEquals('event response', $response->getContent());
	}

	/** @test */
	public function exceptionIsNotHandledIfCatchIsFalse()
	{
		$kernel = $this->makeKernel();
		$this->router->shouldReceive('dispatch')->once()->andThrow(new \Exception('router exception'));
		$this->setExpectedException('Exception', 'router exception');
		$response = $kernel->handle(Request::create('/'), 1, false);
	}

	/** @test */
	public function exceptionIsHandled()
	{
		$kernel = $this->makeKernel(false);
		$this->router->shouldReceive('dispatch')->once()->andThrow($e = new \Exception('router exception'));
		$this->errorHandler->shouldReceive('handle')->once()->with($e)->andReturn(new Response('error handler response'));
		$response = $kernel->handle(Request::create('/'));
		$this->assertEquals('error handler response', $response->getContent());
	}

	/** @test */
	public function responseIsReturnedEvenIfFilterResponseThrowsException()
	{
		$kernel = $this->makeKernel();
		$this->router->shouldReceive('dispatch')->once()->andThrow($e = new \Exception('router exception'));
		$this->errorHandler->shouldReceive('handle')->once()->with($e)->andReturn(new Response('error handler response'));
		$this->eventDispatcher->addListener('kernel.response', function() { throw new \Exception('listener exception'); });
		$response = $kernel->handle(Request::create('/'));
		$this->assertEquals('error handler response', $response->getContent());
	}

	/** @test */
	public function exceptionListenerCanReturnResponse()
	{
		$kernel = $this->makeKernel();
		$this->router->shouldReceive('dispatch')->once()->andThrow($e = new \Exception('router exception'));
		$this->eventDispatcher->addListener('kernel.exception', function($event) {
			$event->setResponse(new Response('exception listener response'));
		});
		$response = $kernel->handle(Request::create('/'));
		$this->assertEquals('exception listener response', $response->getContent());
	}

	/** @test */
	public function terminateFiresEvent()
	{
		$kernel = $this->makeKernel();
		$triggered = false;
		$this->eventDispatcher->addListener('kernel.terminate', function() use(&$triggered) {
			$triggered = true;
		});
		$kernel->terminate(Request::create('/'), new Response(''));
		$this->assertTrue($triggered);
	}
}
