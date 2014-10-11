<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use Autarky\Container\Container;

class ControllerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function makeController($container = null)
	{
		$controller = new StubController;
		$controller->setContainer($container ?: new Container);
		return $controller;
	}

	protected function mockContainer()
	{
		return m::mock('Autarky\Container\Container');
	}

	/** @test */
	public function renderMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Autarky\Templating\TemplatingEngine')
			->once()->andReturn($mockTemplating = m::mock('Autarky\Templating\TemplatingEngine'));
		$mockTemplating->shouldReceive('render')->with('template', ['foo' => 'bar'])->once();
		$ctrl->call('render', ['template', ['foo' => 'bar']]);
	}

	/** @test */
	public function urlMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Autarky\Routing\UrlGenerator')
			->once()->andReturn($mockUrl = m::mock('Autarky\Routing\UrlGenerator'));
		$mockUrl->shouldReceive('getRouteUrl')->with('route', ['foo' => 'bar'])->once();
		$ctrl->call('url', ['route', ['foo' => 'bar']]);
	}

	/** @test */
	public function getSessionMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\Session\Session')->once();
		$ctrl->call('getSession');
	}

	/** @test */
	public function flashMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\Session\Session')
			->once()->andReturn($session = m::mock('Symfony\Component\HttpFoundation\Session\Session'));
		$session->shouldReceive('getFlashBag') ->once()
			->andReturn($flashBag = m::mock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag'));
		$flashBag->shouldReceive('set')->with('foo', 'bar')->once();
		$ctrl->call('flash', ['foo', 'bar']);
	}

	/** @test */
	public function flashMessagesWithNonArray()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\Session\Session')
			->once()->andReturn($session = m::mock('Symfony\Component\HttpFoundation\Session\Session'));
		$session->shouldReceive('getFlashBag')->once()->andReturn($flashBag = m::mock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag'));
		$flashBag->shouldReceive('add')->with('_messages', 'message')->once();
		$ctrl->call('flashMessages', ['message']);
	}

	/** @test */
	public function flashMessagesWithArray()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\Session\Session')
			->once()->andReturn($session = m::mock('Symfony\Component\HttpFoundation\Session\Session'));
		$session->shouldReceive('getFlashBag')->once()->andReturn($flashBag = m::mock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag'));
		$flashBag->shouldReceive('add')->with('_messages', 'message1')->once();
		$flashBag->shouldReceive('add')->with('_messages', 'message2')->once();
		$ctrl->call('flashMessages', [['message1', 'message2']]);
	}

	/** @test */
	public function flashInputMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\RequestStack')
			->once()->andReturn($stack = new \Symfony\Component\HttpFoundation\RequestStack());
		$stack->push($request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/foo'));
		$request->request->replace(['foo' => 'bar']);
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\Session\Session')
			->once()->andReturn($session = m::mock('Symfony\Component\HttpFoundation\Session\Session'));
		$session->shouldReceive('getFlashBag')->once()->andReturn($flashBag = m::mock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag'));
		$flashBag->shouldReceive('set')->with('_old_input', ['foo' => 'bar'])->once();
		$ctrl->call('flashInput');
	}

	/** @test */
	public function getOldInputMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\HttpFoundation\Session\Session')
			->once()->andReturn($session = m::mock('Symfony\Component\HttpFoundation\Session\Session'));
		$session->shouldReceive('getFlashBag')->once()->andReturn($flashBag = m::mock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag'));
		$flashBag->shouldReceive('peek')->with('_old_input', [])->once();
		$ctrl->call('getOldInput');
	}

	/** @test */
	public function getEventDispatcherMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\EventDispatcher\EventDispatcherInterface')->once();
		$ctrl->call('getEventDispatcher');
	}

	/** @test */
	public function dispatchEventMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->once()->andReturn($dispatcher = m::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface'));
		$event = new \Symfony\Component\EventDispatcher\Event;
		$dispatcher->shouldReceive('dispatch')->with('name', $event)->once();
		$ctrl->call('dispatchEvent', ['name', $event]);
	}

	/** @test */
	public function getLoggerMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Psr\Log\LoggerInterface')->once();
		$ctrl->call('getLogger');
	}

	/** @test */
	public function logMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Psr\Log\LoggerInterface')->once()
			->andReturn($logger = m::mock('Psr\Log\LoggerInterface'));
		$logger->shouldReceive('log')->with('debug', 'foo', ['bar'])->once();
		$ctrl->call('log', ['debug', 'foo', ['bar']]);
	}

	/** @test */
	public function responseMethod()
	{
		$ctrl = $this->makeController();
		$response = $ctrl->call('response', ['foo', 202]);
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('foo', $response->getContent());
		$this->assertEquals(202, $response->getStatusCode());
	}

	/** @test */
	public function redirectMethod()
	{
		$ctrl = $this->makeController($container = $this->mockContainer());
		$container->shouldReceive('resolve')->with('Autarky\Routing\UrlGenerator')
			->once()->andReturn($urlGenerator = m::mock('Autarky\Routing\UrlGenerator'));
		$urlGenerator->shouldReceive('getRouteUrl')->with('foo', ['bar' => 'baz'])->once()
			->andReturn('fake_url');
		$response = $ctrl->call('redirect', ['foo', ['bar' => 'baz']]);
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
		$this->assertEquals(302, $response->getStatusCode());
		$this->assertEquals('fake_url', $response->getTargetUrl());
	}

	/** @test */
	public function jsonMethod()
	{
		$ctrl = $this->makeController();
		$response = $ctrl->call('json', [['foo' => 'bar'], 203]);
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
		$this->assertEquals('{"foo":"bar"}', $response->getContent());
		$this->assertEquals(203, $response->getStatusCode());
	}
}

class StubController extends \Autarky\Routing\Controller
{
	public function call($method, array $args = array())
	{
		return call_user_func_array([$this, $method], $args);
	}
}
