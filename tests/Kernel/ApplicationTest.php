<?php
namespace Autarky\Tests\Kernel;

use Mockery as m;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Autarky\Tests\TestCase;
use Autarky\Kernel\Application;
use Autarky\Config\ArrayStore;
use Autarky\Container\Container;

class ApplicationTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeApp($response = null)
	{
		$app = $this->makeApplication();
		if ($response) {
			$mockRouter = m::mock('Autarky\Routing\RouterInterface');
			$mockRouter->shouldReceive('dispatch')->andReturn(new Response($response));
			$app->getContainer()->share('Autarky\Routing\RouterInterface', $mockRouter);
		}
		return $app;
	}

	/** @test */
	public function environmentClosureIsResolvedOnBoot()
	{
		$app = $this->makeApp();
		$app->setEnvironment(function() { return 'testenv'; });
		$app->boot();
		$this->assertEquals('testenv', $app->getEnvironment());
	}

	/** @test */
	public function environmentCannotBeSetAfterBoot()
	{
		$app = $this->makeApp();
		$app->setEnvironment(function() { return 'testenv'; });
		$app->boot();
		$this->setExpectedException('RuntimeException');
		$app->setEnvironment(function() { return 'testenv2'; });
	}

	/** @test */
	public function prematureGettingOfEnvironmentThrowsException()
	{
		$app = $this->makeApp();
		$app->setEnvironment(function() { return 'testenv'; });
		$this->setExpectedException('RuntimeException');
		$app->getEnvironment();
	}

	/** @test */
	public function pushStringMiddleware()
	{
		$app = $this->makeApp('foo');
		$app->addMiddleware(__NAMESPACE__.'\MiddlewareA');
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('fooa', $response->getContent());
	}

	/** @test */
	public function pushClosureMiddleware()
	{
		$app = $this->makeApp('foo');
		$app->addMiddleware(function($app) { return new MiddlewareA($app); });
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('fooa', $response->getContent());
	}

	/** @test */
	public function pushArrayMiddleware()
	{
		$app = $this->makeApp('foo');
		$app->addMiddleware([__NAMESPACE__.'\MiddlewareC', 'c']);
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('foobc', $response->getContent());
	}

	/** @test */
	public function pushMiddlewarePriority()
	{
		$app = $this->makeApp('foo');
		$app->addMiddleware(__NAMESPACE__.'\MiddlewareA', 1);
		$app->addMiddleware(__NAMESPACE__.'\MiddlewareB', -1);
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('fooba', $response->getContent());
	}

	/** @test */
	public function configCallbackIsCalledOnBoot()
	{
		$app = $this->makeApp();
		$booted = false;
		$app->config(function() use(&$booted) { $booted = true; });
		$this->assertEquals(false, $booted);
		$app->boot();
		$this->assertEquals(true, $booted);
	}

	/** @test */
	public function configCallbackIsCalledImmediatelyIfBooted()
	{
		$app = $this->makeApp();
		$booted = false;
		$app->boot();
		$app->config(function() use(&$booted) { $booted = true; });
		$this->assertEquals(true, $booted);
	}

	/** @test */
	public function serviceProvidersAreCalled()
	{
		$app = $this->makeApp();
		$app->getConfig()->set('app.providers', [__NAMESPACE__.'\\StubServiceProvider']);
		$app->boot();
		$this->assertTrue(StubServiceProvider::$called);
	}
}

abstract class AbstractMiddleware implements HttpKernelInterface
{
	public function __construct($app)
	{
		$this->app = $app;
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$response = $this->app->handle($request, $type, $catch);
		$this->transform($response);
		return $response;
	}

	protected abstract function transform(Response $response);
}

class MiddlewareA extends AbstractMiddleware
{
	public function transform(Response $response)
	{
		$response->setContent($response->getContent().'a');
	}
}

class MiddlewareB extends AbstractMiddleware
{
	public function transform(Response $response)
	{
		$response->setContent($response->getContent().'b');
	}
}

class MiddlewareC extends AbstractMiddleware
{
	protected $affix;

	public function __construct($app, $affix)
	{
		parent::__construct($app);
		$this->affix = $affix;
	}

	public function transform(Response $response)
	{
		$affix = 'b' . $this->affix;
		$response->setContent($response->getContent().$affix);
	}
}

class StubServiceProvider extends \Autarky\Kernel\ServiceProvider
{
	public static $called = false;
	public function register()
	{
		static::$called = true;
	}
}
