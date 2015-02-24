<?php

use Mockery as m;

use Autarky\Kernel\HttpKernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

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
		$this->errorHandler->shouldReceive('handle')->andReturnUsing(function($e) { throw $e; })->byDefault();
		$this->requests = new \Symfony\Component\HttpFoundation\RequestStack;
		$this->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher;
		return new HttpKernel($this->router, $this->requests, $this->errorHandler, $events ? $this->eventDispatcher : null);
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
		$this->eventDispatcher->addListener(KernelEvents::REQUEST, function($event) {
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
		$this->eventDispatcher->addListener(KernelEvents::RESPONSE, function($event) {
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
		$this->eventDispatcher->addListener(KernelEvents::RESPONSE, function() { throw new \Exception('listener exception'); });
		$response = $kernel->handle(Request::create('/'));
		$this->assertEquals('error handler response', $response->getContent());
	}

	/** @test */
	public function exceptionListenerCanReturnResponse()
	{
		$kernel = $this->makeKernel();
		$this->router->shouldReceive('dispatch')->once()->andThrow($e = new \Exception('router exception'));
		$this->eventDispatcher->addListener(KernelEvents::EXCEPTION, function($event) {
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
		$this->eventDispatcher->addListener(KernelEvents::TERMINATE, function() use(&$triggered) {
			$triggered = true;
		});
		$kernel->terminate(Request::create('/'), new Response(''));
		$this->assertTrue($triggered);
	}

	/** @test */
	public function requestStackIsCorrect()
	{
		$kernel = $this->makeKernel();
		$triggered = false;
		$this->router->shouldReceive('dispatch')->once()
			->andReturnUsing(function() use(&$triggered) {
				$triggered = true;
				$this->assertEquals(1, count($this->requests));
				return new Response('foo');
			});
		$response = $kernel->handle($request = Request::create('/'));
		$kernel->terminate($request, $response);
	}

	/** @test */
	public function nestedRequestStackIsCorrect()
	{
		$kernel = $this->makeKernel();
		$counter = 0;
		$first = function($request) use(&$counter, $kernel) {
			$asserts = function() use($request) {
				$this->assertSame(
					$request,
					$this->requests->getCurrentRequest()
				);
				$this->assertSame(
					$this->requests->getCurrentRequest(),
					$this->requests->getMasterRequest()
				);
				$this->assertNull($this->requests->getParentRequest());
				$this->assertEquals(1, count(static::readAttribute($this->requests, 'requests')));
			};
			$counter++;
			$asserts();
			$innerRequest = clone $request;
			$result = $kernel->handle($innerRequest);
			$asserts();
			return $result;
		};
		$second = function($request) use(&$counter) {
			$counter++;
			$this->assertNotNull($this->requests->getParentRequest());
			$this->assertSame(
				$this->requests->getMasterRequest(),
				$this->requests->getParentRequest()
			);
			$this->assertNotSame(
				$this->requests->getCurrentRequest(),
				$this->requests->getMasterRequest()
			);
			$this->assertEquals(2, count(static::readAttribute($this->requests, 'requests')));
			return new Response('foo');
		};
		$this->router->shouldReceive('dispatch')->twice()
			->andReturnUsing($first, $second);
		$response = $kernel->handle($request = Request::create('/'));
		$kernel->terminate($request, $response);
		$this->assertEquals(2, $counter);
		$this->assertEquals('foo', $response->getContent());
	}
}
