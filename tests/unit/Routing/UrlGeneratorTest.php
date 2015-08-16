<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Autarky\Container\Container;
use Autarky\Events\EventDispatcher;
use Autarky\Events\ListenerResolver;
use Autarky\Routing\Router;
use Autarky\Routing\Invoker;
use Autarky\Routing\UrlGenerator;

class UrlGeneratorTest extends PHPUnit_Framework_TestCase
{
	protected function makeRouterAndGenerator($request = null)
	{
		$container = new Container;
		$router = new Router(
			new Invoker($container),
			new EventDispatcher(new ListenerResolver($container))
		);
		$requests = new RequestStack;
		if ($request) $requests->push($request);
		return [$router, new UrlGenerator($router, $requests)];
	}

	/**
	 * @test
	 * @dataProvider getUrlGenerationData
	 */
	public function getRouteUrlReturnsCorrectUrl($path, array $params, $expected)
	{
		list($router, $url) = $this->makeRouterAndGenerator(Request::create('/'));
		$router->addRoute('get', $path, function() {}, 'name');
		$this->assertEquals('//localhost'.$expected, $url->getRouteUrl('name', $params));
		$this->assertEquals($expected, $url->getRouteUrl('name', $params, true));
	}

	public function getUrlGenerationData()
	{
		return [
			['/foo/{v}', ['bar'], '/foo/bar'],
			['/foo/{v:[a-z]+}', ['bar'], '/foo/bar'],
			['/foo/{v:[0-9]+}', [123], '/foo/123'],
			['/{v1}/{v2}', ['v1', 'v2'], '/v1/v2'],
			['/{v1}', ['v1', 'foo'=>'bar'], '/v1?foo=bar'],
		];
	}

	/** @test */
	public function tooFewParamsThrowsException()
	{
		list($router, $url) = $this->makeRouterAndGenerator(Request::create('/'));
		$router->addRoute('get', '/{v1}/{v2}', function() {}, 'name');
		$this->setExpectedException('InvalidArgumentException');
		$url->getRouteUrl('name', ['v1']);
	}

	/** @test */
	public function getRouteUrlCanReturnAbsoluteOrRelativePath()
	{
		list($router, $url) = $this->makeRouterAndGenerator($request = Request::create('/'));
		$router->addRoute('get', '/foo/bar', function() {}, 'name');
		$this->assertEquals('//localhost/foo/bar', $url->getRouteUrl('name', [], false));
		$this->assertEquals('/foo/bar', $url->getRouteUrl('name', [], true));
	}

	/** @test */
	public function routeUrlIsBasedOnCurrentRequestRoot()
	{
		list($router, $url) = $this->makeRouterAndGenerator($request = Request::create('http://some.host/'));
		$router->addRoute('get', 'foo/{v}', function() {}, 'name');
		$this->assertEquals('//some.host/foo/bar', $url->getRouteUrl('name', ['bar']));
	}

	/** @test */
	public function canGenerateRelativeAssetUrls()
	{
		list($router, $url) = $this->makeRouterAndGenerator($request = Request::create('http://some.host/'));
		$this->assertEquals('//some.host/foo/bar', $url->getAssetUrl('foo/bar', false));
		$this->assertEquals('/foo/bar', $url->getAssetUrl('foo/bar', true));
	}

	/** @test */
	public function canGenerateRelativeUrlsInSubdirectory()
	{
		$server = [
			'PHP_SELF' => '/path/to/subdir/index.php',
			'SCRIPT_FILENAME' => '/subdir/index.php',
			'SCRIPT_NAME' => '/subdir/index.php',
		];
		$request = Request::create('http://some.host/subdir/bar/baz', 'GET', [], [], [], $server);
		list($router, $url) = $this->makeRouterAndGenerator($request);
		$router->addRoute('get', 'foo/bar', function() {}, 'name');
		$this->assertEquals('/subdir/foo/bar', $url->getAssetUrl('foo/bar', true));
		$this->assertEquals('/subdir/foo/bar', $url->getRouteUrl('name', [], true));
	}

	/** @test */
	public function canSetAssetRoot()
	{
		list($router, $url) = $this->makeRouterAndGenerator($request = Request::create('http://localhost/foo'));
		$url->setAssetRoot('//some.cdn.com');
		$this->assertEquals('//some.cdn.com/foo/bar', $url->getAssetUrl('foo/bar'));
	}
}
