<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Autarky\Container\IlluminateContainer;
use Autarky\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends PHPUnit_Framework_TestCase
{
	public function makeRouter()
	{
		return new Router(new IlluminateContainer);
	}

	/** @test */
	public function basicRouting()
	{
		$router = $this->makeRouter();
		$router->addRoute('get', '/one/{v}', function($r, $v) { return 'v:'.$v; });
		$response = $router->dispatch(Request::create('/one/foo'));
		$this->assertEquals('v:foo', $response->getContent());
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
