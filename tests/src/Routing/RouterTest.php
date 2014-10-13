<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

use Autarky\Container\Container;
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
		return new Router(new Invoker(new Container));
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
		$router->defineFilter('foo', function() { return 'from filter'; });
		$router->group(['before' => 'foo'], function(Router $router) use(&$route) {
			$route = $router->addRoute('get', '/foo', function() {});
		});
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('from filter', $response->getContent());
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

	/** @test */
	public function beforeFiltersAreCalled()
	{
		$router = $this->makeRouter();
		$route = $router->addRoute(['get'], '/foo', function() { return 'foo'; });
		$route->addBeforeFilter(function() { return; });
		$route->addBeforeFilter(function() { return 'bar'; });
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('bar', $response->getContent());
	}

	/** @test */
	public function afterFiltersAreCalled()
	{
		$router = $this->makeRouter();
		$route = $router->addRoute(['get'], '/foo', function() { return 'foo'; });
		$route->addAfterFilter(function() { return; });
		$route->addAfterFilter(function(Response $r) { $r->setContent('baz'); });
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('baz', $response->getContent());
	}

	/** @test */
	public function filtersArePassedRouteRequestAndResponse()
	{
		$router = $this->makeRouter();
		$route = $router->addRoute(['get'], '/foo', function() { return 'foo'; });
		$route->addBeforeFilter(function(Route $route, Request $request) {

		});
		$route->addAfterFilter(function(Route $route, Request $request, Response $response) {

		});
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('foo', $response->getContent());
	}

	/** @test */
	public function filtersAreResolvedFromContainer()
	{
		$router = new Router(new Invoker($container = new Container));
		$route = $router->addRoute(['get'], '/foo', function() { return 'foo'; });
		$route->addBeforeFilter('StubFilter:f');
		$container->instance('StubFilter', new StubFilter);
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('f', $response->getContent());
	}

	/** @test */
	public function filtersResolvedFromTheContainerCallFilterMethodByDefault()
	{
		$router = new Router(new Invoker($container = new Container));
		$route = $router->addRoute(['get'], '/foo', function() { return 'foo'; });
		$route->addBeforeFilter('StubFilter');
		$container->instance('StubFilter', new StubFilter);
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('filter', $response->getContent());
	}

	/** @test */
	public function filtersAndRespondersCanBeSeparate()
	{
		$router = new Router(new Invoker($container = new Container));
		$route = $router->addRoute(['get'], '/foo', function() { return 'foo'; });
		$route->addBeforeFilter(['StubFilter', 'StubResponder']);
		$container->instance('StubFilter', new StubFilter);
		$container->instance('StubResponder', new StubResponder);
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('respond', $response->getContent());
	}

	/**
	 * @test
	 * @dataProvider getPathData
	 */	
	public function pathsAreNormalized($path, $expected)
	{
		$router = $this->makeRouter();
		$route = $router->addRoute(['get'], $path, function() {});
		$this->assertEquals($expected, $route->getPath());
		// throws an exception if no route was found
		$router->dispatch(Request::create($expected));
	}

	public function getPathData()
	{
		return [
			['', '/'],
			['/', '/'],
			['foo', '/foo'],
			['foo/', '/foo'],
			['/foo', '/foo'],
			['/foo/', '/foo'],
		];
	}

	/**
	 * @test
	 * @dataProvider getPrefixPathData
	 */
	public function prefixesCannotMessUpUris($prefix, $path, $expected)
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => $prefix], function(Router $router) use($path, &$route) {
			$route = $router->addRoute(['get'], $path, function() { return; });
		});
		$this->assertEquals($expected, $route->getPath());
		// throws an exception if no route was found
		$router->dispatch(Request::create($expected));
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
