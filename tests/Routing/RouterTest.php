<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

use Autarky\Container\Container;
use Autarky\Routing\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
	public function makeRouter($request = null)
	{
		$router = new Router(new Container);
		if ($request) $router->setCurrentRequest($request);
		return $router;
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
	public function urlGeneration()
	{
		$router = $this->makeRouter(Request::create('/'));
		$router->addRoute('get', '/foo/{v}', function() {}, 'name');
		$this->assertEquals('http://localhost/foo/bar', $router->getRouteUrl('name', ['bar']));
		$this->assertEquals('/foo/bar', $router->getRouteUrl('name', ['bar'], true));
	}

	/** @test */
	public function canAddRoutesWithoutLeadingSlash()
	{
		$router = $this->makeRouter();
		$router->addRoute('get', 'foo/{v}', function() { return 'test'; }, 'name');
		$response = $router->dispatch(Request::create('/foo/foo'));
		$this->assertEquals('test', $response->getContent());
		$this->assertEquals('http://localhost/foo/bar', $router->getRouteUrl('name', ['bar']));
		$this->assertEquals('/foo/bar', $router->getRouteUrl('name', ['bar'], true));
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
		$router->group(['before' => 'foo'], function($router) use(&$route) {
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
		$router->group(['prefix' => 'foo'], function($router) use(&$route) {
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
		$route->addAfterFilter(function() { return 'baz'; });
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('baz', $response->getContent());
	}

	/**
	 * @test
	 * @dataProvider getPrefixPathData
	 */
	public function prefixesCannotMessUpUris($prefix, $path, $expectedPath)
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => $prefix], function($router) use($path, &$route) {
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
