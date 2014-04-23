<?php
namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;

use Autarky\Routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function pathCanBeGenerated()
	{
		$route = new Route(['get'], '/foo/{v1}/{v2}', 'handler');
		$this->assertEquals('/foo/bar/baz', $route->getPath(['bar', 'baz']));
	}

	/** @test */
	public function beforeFiltersAreCalled()
	{
		$route = new Route(['get'], '/foo', function() { return 'foo'; });
		$route->addBeforeFilter(function() { return; });
		$route->addBeforeFilter(function() { return 'bar'; });
		$this->assertEquals('bar', $route->run());
	}

	/** @test */
	public function afterFiltersAreCalled()
	{
		$route = new Route(['get'], '/foo', function() { return 'foo'; });
		$route->addAfterFilter(function() { return; });
		$route->addAfterFilter(function() { return 'baz'; });
		$this->assertEquals('baz', $route->run());
	}

	/** @test */
	public function routeCanBeRan()
	{
		$route = new Route(['get'], '/', function() { return 'foo'; });
		$this->assertEquals('foo', $route->run());
	}

	/** @test */
	public function routeWithClassHandlerCanBeRan()
	{
		$route = new Route(['get'], '/', __NAMESPACE__.'\RouteHandlerStub:handle');
		$this->assertEquals('foo', $route->run());
	}
}

class RouteHandlerStub
{
	public function handle()
	{
		return 'foo';
	}
}
