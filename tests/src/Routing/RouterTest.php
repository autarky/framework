<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

use Autarky\Container\Container;
use Autarky\Events\EventDispatcher;
use Autarky\Events\ListenerResolver;
use Autarky\Routing\Route;
use Autarky\Routing\Router;
use Autarky\Routing\Invoker;

class RouterTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeRouter()
	{
		$container = new Container;
		return new Router(
			new Invoker($container),
			new EventDispatcher(new ListenerResolver($container))
		);
	}

	/** @test */
	public function basicRouting()
	{
		$router = $this->makeRouter();
		$router->addRoute('get', '/foo/{v}', function($v) { return 'v:'.$v; });
		$response = $router->dispatch(Request::create('/foo/bar'));
		$this->assertEquals('v:bar', $response->getContent());
	}

	/** @test */
	public function routeNotFoundThrowsException()
	{
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
		$router = $this->makeRouter();
		$router->dispatch(Request::create('/foo'));
	}

	/** @test */
	public function methodNotAllowedThrowsException()
	{
		$this->setExpectedException('Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException');
		$router = $this->makeRouter();
		$router->addRoute('get', '/foo', function() {});
		$router->dispatch(Request::create('/foo', 'post'));
	}

	/** @test */
	public function routeGroupFilter()
	{
		$router = $this->makeRouter();
		$router->addBeforeFilter('foo', function($event) {
			$event->setResponse('from filter');
		});
		$router->group(['before' => 'foo'], function(Router $router) use(&$route) {
			$route = $router->addRoute('get', '/foo', function() { return 'from route'; });
		});
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('from filter', $response->getContent());
	}

	/** @test */
	public function routeFilterCanChangeController()
	{
		$router = $this->makeRouter();
		$route = $router->addRoute('get', '/foo', function() { return 'old controller'; });
		$router->addBeforeFilter('bar', function($event) {
			$event->setController(function() { return 'new controller'; });
		});
		$route->addBeforeFilter('bar');
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('new controller', $response->getContent());
	}

	/** @test */
	public function routeGroupPrefix()
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => 'foo'], function(Router $router) use(&$route) {
			$route = $router->addRoute('get', '/bar', function() {});
		});
		$this->assertEquals('/foo/bar', $route->getPath([]));
	}

	/**
	 * @test
	 * @dataProvider getPrefixPathData
	 */
	public function prefixesCannotMessUpUris($prefix, $path, $expectedPath)
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => $prefix], function(Router $router) use($path, &$route) {
			$route = $router->addRoute(['get'], $path, function() { return; });
		});
		$this->assertEquals($expectedPath, $route->getPath());
	}

	public function getPrefixPathData()
	{
		return array_map(function($prefix, $path) {
			return [$prefix, $path, '/foo/bar'];
		}, ['foo', '/foo', 'foo/', '/foo/'], ['bar', '/bar', 'bar/', '/bar/']);
	}
}

class StubFilter {
	public function f() {
		return __FUNCTION__;
	}
	public function filter() {
		return __FUNCTION__;
	}
}

class StubResponder {
	public function respond() {
		return __FUNCTION__;
	}
}
