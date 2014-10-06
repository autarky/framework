<?php
namespace Autarky\Tests\Session;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeMiddleware($kernel, $app)
	{
		return new \Autarky\Session\Middleware($kernel, $app);
	}

	public function makeKernel(Response $response)
	{
		$mock = m::mock('Symfony\Component\HttpKernel\HttpKernelInterface');
		$mock->shouldReceive('handle')->andReturn($response)->byDefault();
		return $mock;
	}

	public function makeApplication($session)
	{
		$app = new \Autarky\Kernel\Application('testing', []);
		$app->setContainer($container = new \Autarky\Container\Container);
		$container->instance('Symfony\Component\HttpFoundation\Session\SessionInterface', $session);
		$app->setConfig(new \Autarky\Config\ArrayStore);
		return $app;
	}

	public function makeSession($name = 'session')
	{
		return new \Symfony\Component\HttpFoundation\Session\Session(
			new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage($name)
		);
	}

	/** @test */
	public function subRequestsArePassedRightThrough()
	{
		$app = $this->makeApplication('fake_session');
		$kernel = $this->makeKernel(new Response('foo'));
		$middleware = $this->makeMiddleware($kernel, $app);
		$middleware->handle($request = Request::create('/'), HttpKernelInterface::SUB_REQUEST);
		$this->assertEquals(null, $request->getSession());
	}

	/** @test */
	public function sessionIsAttachedToRequestButIsNotStarted()
	{
		$app = $this->makeApplication($session = $this->makeSession());
		$kernel = $this->makeKernel(new Response('foo'));
		$middleware = $this->makeMiddleware($kernel, $app);
		$middleware->handle($request = Request::create('/'));
		$this->assertSame($session, $request->getSession());
		$this->assertEquals(false, $session->isStarted());
	}

	/** @test */
	public function sessionIsAttachedToResponseIfStarted()
	{
		$app = $this->makeApplication($session = $this->makeSession());
		$session->start();
		$kernel = $this->makeKernel(new Response('foo'));
		$middleware = $this->makeMiddleware($kernel, $app);
		$response = $middleware->handle($request = Request::create('/'));
		$cookies = $response->headers->getCookies();
		$this->assertEquals(1, count($cookies));
		$cookie = $cookies[0];
		$this->assertEquals($session->getName(), $cookie->getName());
		$this->assertEquals($session->getId(), $cookie->getValue());
	}

	/** @test */
	public function sessionGetsIdOfRequestCookie()
	{
		$app = $this->makeApplication($session = $this->makeSession());
		$kernel = $this->makeKernel(new Response('foo'));
		$middleware = $this->makeMiddleware($kernel, $app);
		$request = Request::create('/');
		$request->cookies->set($session->getName(), '1234');
		$middleware->handle($request);
		$this->assertEquals('1234', $session->getId());
	}
}
