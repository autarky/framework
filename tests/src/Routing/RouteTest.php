<?php

use Symfony\Component\HttpFoundation\Request;

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
	public function tooFewParamsThrowsException()
	{
		$route = new Route(['get'], '/{v1}/{v2}', function() { return 'OK'; });
		$this->setExpectedException('InvalidArgumentException');
		$route->getPath(['v1']);
	}

	/** @test */
	public function extraParamsAreAddedAsQueryString()
	{
		$route = new Route(['get'], '/{v1}', function() { return 'OK'; });
		$path = $route->getPath(['v1', 'foo' => 'bar']);
		$this->assertEquals('/v1?foo=bar', $path);
	}
}

class RouteHandlerStub
{
	public function handle()
	{
		return 'foo';
	}

	public function handleRequest(Request $request, $var)
	{
		return $var;
	}
}
