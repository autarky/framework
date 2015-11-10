<?php

use FastRoute\RouteParser\Std as RouteParser;
use Mockery as m;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

use Autarky\Container\Container;
use Autarky\Events\EventDispatcher;
use Autarky\Events\ListenerResolver;
use Autarky\Routing\Route;
use Autarky\Routing\Router;
use Autarky\Routing\Invoker;

class RouterTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeRouter()
	{
		$container = new Container;
		return new Router(
			new RouteParser,
			new Invoker($container),
			new EventDispatcher(new ListenerResolver($container))
		);
	}

	/** @test */
	public function addRouteAndDispatchRequest()
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
	public function addFiltersViaRouteGrouping()
	{
		$router = $this->makeRouter();
		$router->onBefore('foo', function($event) {
			$event->setResponse('from filter');
		});
		$router->group(['before' => 'foo'], function(Router $router) use(&$route) {
			$route = $router->addRoute('get', '/foo', function() { return 'from route'; });
		});
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('from filter', $response->getContent());
	}

	/** @test */
	public function beforeFilterCanChangeController()
	{
		$router = $this->makeRouter();
		$route = $router->addRoute('get', '/foo', function() { return 'old controller'; });
		$router->onBefore('bar', function($event) {
			$event->setController(function() { return 'new controller'; });
		});
		$route->addBeforeFilter('bar');
		$response = $router->dispatch(Request::create('/foo'));
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('new controller', $response->getContent());
	}

	/** @test */
	public function pathPrefixCanBeSetViaRouteGroups()
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => 'foo'], function(Router $router) use(&$route) {
			$route = $router->addRoute('get', '/bar', function() {});
		});
		$this->assertEquals('/foo/bar', $route->getPattern());
	}

	/**
	 * @test
	 * @dataProvider getPathData
	 */
	public function routePathsAreNormalized($path, $expected)
	{
		$router = $this->makeRouter();
		$route = $router->addRoute(['get'], $path, function() {});
		$this->assertEquals($expected, $route->getPattern());
		// throws an exception if no route was found
		$router->dispatch(Request::create($expected));
	}

	public function getPathData()
	{
		return [
			['', '/'],
			['/', '/'],
			['foo', '/foo'],
			['foo/', '/foo'],
			['/foo/', '/foo'],
			['/foo', '/foo'],
		];
	}

	/**
	 * @test
	 * @dataProvider getRootPathData
	 */	
	public function rootPathAlwaysWorks($requestPath, $scriptName = null)
	{
		$server = [
			'SCRIPT_FILENAME' => $scriptName,
			'SCRIPT_NAME' => $scriptName,
		];
		$request = Request::create($requestPath, 'GET', [], [], [], $server);
		$router = $this->makeRouter();
		$route = $router->addRoute(['get'], '/', function() {});
		// throws an exception if no route was found
		$router->dispatch($request);
	}

	public function getRootPathData()
	{
		return [
			['http://localhost'],
			['http://localhost/'],
			['http://sub.localhost'],
			['http://sub.localhost/'],
			['http://localhost/foo/', '/foo/index.php'],
			['http://sub.localhost/foo/', '/foo/index.php'],
			// https://github.com/symfony/symfony/pull/13039
			// ['http://localhost/foo', '/foo/index.php'],
			// ['http://sub.localhost/foo', '/foo/index.php'],
		];
	}

	/**
	 * @test
	 * @dataProvider getPrefixPathData
	 */
	public function prefixesCannotMessUpPaths($prefix, $path, $expected)
	{
		$router = $this->makeRouter();
		$router->group(['prefix' => $prefix], function(Router $router) use($path, &$route) {
			$route = $router->addRoute(['get'], $path, function() { return; });
		});
		$this->assertEquals($expected, $route->getPattern());
		// throws an exception if no route was found
		$router->dispatch(Request::create($expected));
	}

	public function getPrefixPathData()
	{
		return array_map(function($prefix, $path) {
			return [$prefix, $path, '/foo/bar'];
		}, ['foo', '/foo', 'foo/', '/foo/'], ['bar', '/bar', 'bar/', '/bar/']);
	}
}
