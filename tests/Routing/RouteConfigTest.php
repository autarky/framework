<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class RouteConfigTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function routesAreRegistered()
	{
		$router = $this->mockRouter();
		$data = $this->getRouteData();
		$config = $this->makeRouteConfig($router, $data);
		$router->shouldReceive('addRoute')->once()->with(['get'], '/path', 'foo:bar', 'foobar');
		$config->mount();
	}

	/** @test */
	public function routeDataCanBeOverridden()
	{
		$router = $this->mockRouter();
		$data = $this->getRouteData();
		$config = $this->makeRouteConfig($router, $data);
		$config->override('foobar', ['methods' => ['get', 'post']]);
		$router->shouldReceive('addRoute')->once()->with(['get', 'post'], '/path', 'foo:bar', 'foobar');
		$config->mount();
	}

	protected function makeRouteConfig($router, array $routes = array())
	{
		return new \Autarky\Routing\RouteConfig($router, $routes);
	}

	protected function getRouteData()
	{
		return [
			'foobar' => [
				'methods' => ['get'],
				'handler' => 'foo:bar',
				'path' => '/path',
			],
		];
	}

	protected function mockRouter()
	{
		return m::mock('Autarky\Routing\RouterInterface');
	}
}
