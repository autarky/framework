<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Autarky\Container\IlluminateContainer;
use Autarky\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends PHPUnit_Framework_TestCase
{
	public function makeRouter($request = null)
	{
		$router = new Router(new IlluminateContainer);
		if ($request) $router->setCurrentRequest($request);
		return $router;
	}

	/** @test */
	public function basicRouting()
	{
		$router = $this->makeRouter();
		$router->addRoute('get', '/foo/{v}', function($r, $v) { return 'v:'.$v; });
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
		$router->defineFilter('foo', function() { return 'foo'; });
		$router->group(['before' => 'foo'], function($router) use(&$route) {
			$route = $router->addRoute('get', '/foo', function() {});
		});
		$this->assertEquals('foo', $route->run());
	}

	/** @test */
	public function routeGroupPrefix()
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => 'foo'], function($router) use(&$route) {
			$route = $router->addRoute('get', '/bar', function() {});
		});
		$this->assertEquals('foo/bar', $route->getPath([]));
	}
}
