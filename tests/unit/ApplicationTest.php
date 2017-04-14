<?php

use Mockery as m;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Autarky\Tests\TestCase;
use Autarky\Application;
use Autarky\Config\ArrayStore;
use Autarky\Container\Container;

class ApplicationTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function returnResponse($app, $response)
	{
		$mockRouter = m::mock('Autarky\Routing\RouterInterface');
		$mockRouter->shouldReceive('dispatch')->andReturn(new Response($response));
		$app->getContainer()->instance('Autarky\Routing\RouterInterface', $mockRouter);
	}

	/** @test */
	public function environmentClosureIsInvoked()
	{
		$app = $this->makeApplication([], function() { return 'testenv'; });
		$app->boot();
		$this->assertEquals('testenv', $app->getEnvironment());
	}

	/** @test */
	public function prematureGettingOfEnvironmentThrowsException()
	{
		$app = $this->makeApplication([], function() { return 'testenv'; });
		$this->expectException('RuntimeException');
		$app->getEnvironment();
	}

	/** @test */
	public function canAddClassNameStringAsMiddleware()
	{
		$app = $this->makeApplication();
		$this->returnResponse($app, 'foo');
		$app->addMiddleware(__NAMESPACE__.'\MiddlewareA');
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('fooa', $response->getContent());
	}

	/** @test */
	public function canAddClosureAsMiddleware()
	{
		$app = $this->makeApplication();
		$this->returnResponse($app, 'foo');
		$app->addMiddleware(function($app) { return new MiddlewareA($app); });
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('fooa', $response->getContent());
	}

	/** @test */
	public function canAddCallableArrayAsMiddleware()
	{
		$app = $this->makeApplication();
		$this->returnResponse($app, 'foo');
		$app->addMiddleware([__NAMESPACE__.'\MiddlewareC', 'c']);
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('foobc', $response->getContent());
	}

	/** @test */
	public function canSetMiddlewarePriority()
	{
		$app = $this->makeApplication();
		$this->returnResponse($app, 'foo');
		$app->addMiddleware(__NAMESPACE__.'\MiddlewareA', 1);
		$app->addMiddleware(__NAMESPACE__.'\MiddlewareB', -1);
		$response = $app->run(Request::create(''), false);
		$this->assertEquals('fooba', $response->getContent());
	}

	/** @test */
	public function configCallbackIsCalledOnBoot()
	{
		$app = $this->makeApplication();
		$booted = false;
		$app->config(function() use(&$booted) { $booted = true; });
		$this->assertEquals(false, $booted);
		$app->boot();
		$this->assertEquals(true, $booted);
	}

	/** @test */
	public function configCallbackIsCalledImmediatelyIfBooted()
	{
		$app = $this->makeApplication();
		$booted = false;
		$app->boot();
		$app->config(function() use(&$booted) { $booted = true; });
		$this->assertEquals(true, $booted);
	}

	/** @test */
	public function configuratorClassesResolvedOnBoot()
	{
		$mock = m::mock('Autarky\ConfiguratorInterface');
		$app = $this->makeApplication();
		$app->getContainer()->define('MockConfigurator', function() use($mock) {
			return $mock;
		});
		$app->config('MockConfigurator');
		$mock->shouldReceive('configure')->once();
		$app->boot();
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
