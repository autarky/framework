<?php

use Mockery as m;

class RouteConfigurationTest extends PHPUnit\Framework\TestCase
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
		$config = $this->makeConfig($data, 'namespace');
		$router->shouldReceive('addRoute')->once()
			->with(['get'], '/path', ['foo', 'bar'], 'namespace:foobar', []);
		$config->mount($router);
	}

	/** @test */
	public function routeDataCanBeOverridden()
	{
		$router = $this->mockRouter();
		$data = $this->getRouteData();
		$config = $this->makeConfig($data);
		$config->override('foobar', ['methods' => ['get', 'post']]);
		$router->shouldReceive('addRoute')->once()
			->with(['get', 'post'], '/path', ['foo', 'bar'], 'foobar', []);
		$config->mount($router);
	}

	protected function makeConfig(array $routes, $namespace = null)
	{
		return new \Autarky\Routing\Configuration($routes, $namespace);
	}

	protected function getRouteData()
	{
		return [
			'foobar' => [
				'methods' => ['get'],
				'controller' => ['foo', 'bar'],
				'path' => '/path',
			],
		];
	}

	/** @test */
	public function canRegisterMultiControllers()
	{
		$router = $this->mockRouter();
		$config = $this->makeConfig([
			'name' => [
				'path' => '/path',
				'methods' => [
					'get' => ['Controller', 'get'],
					'post' => ['Controller', 'post'],
				],
			],
		]);
		$router->shouldReceive('addRoute')->once()
			->with(['get'], '/path', ['Controller', 'get'], 'name', []);
		$router->shouldReceive('addRoute')->once()
			->with(['post'], '/path', ['Controller', 'post'], null, []);
		$config->mount($router);
	}

	protected function mockRouter()
	{
		return m::mock('Autarky\Routing\RouterInterface');
	}
}
