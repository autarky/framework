<?php
namespace Autarky\Tests\Kernel;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Autarky\Kernel\Application;
use Autarky\Config\ArrayStore;
use Autarky\Container\IlluminateContainer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
	public function makeApp($response)
	{
		$app = new Application('testing');
		$app->setContainer(new IlluminateContainer);
		$app->setConfig(new ArrayStore);
		$mockRouter = m::mock('Autarky\Routing\RouterInterface');
		$mockRouter->shouldReceive('dispatch')->andReturn(new Response($response));
		$app->setRouter($mockRouter);
		return $app;
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
