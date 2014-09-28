<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

use Autarky\Container\Container;
use Autarky\Routing\Route;
use Autarky\Routing\Router;
use Autarky\Routing\Invoker;

class RouterTest extends PHPUnit_Framework_TestCase
{
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
