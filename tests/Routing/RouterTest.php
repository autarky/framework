<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Autarky\Container\IlluminateContainer;
use Autarky\Routing\Router;

class Test extends PHPUnit_Framework_TestCase
{
	public function makeRouter()
	{
		return new Router(new IlluminateContainer);
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
