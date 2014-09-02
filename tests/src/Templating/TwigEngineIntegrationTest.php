<?php
namespace Autarky\Tests\Templating;

use Mockery as m;
use Autarky\Tests\TestCase;
use Autarky\Templating\TwigEngine;
use Autarky\Templating\Template;
use Symfony\Component\HttpFoundation\Request;

class TwigEngineTest extends TestCase
{
	protected function makeApplication(array $providers = array())
	{
		$app = parent::makeApplication();
		$app->getConfig()->set('path.templates', TESTS_RSC_DIR.'/templates');
		$app->getConfig()->set('path.templates-cache', TESTS_RSC_DIR.'/template-cache');
		$app->getConfig()->set('session.driver', 'null');
		$app->getConfig()->set('session.mock', true);
		$app->getConfig()->set('app.debug', true);
		$providers[] = 'Autarky\Templating\TwigServiceProvider';
		$app->getConfig()->set('app.providers', $providers);
		return $app;
	}

	public function makeEngine(array $providers = array())
	{
		$this->app = $this->makeApplication($providers);
		$this->app->boot();
		return $this->app->resolve('Autarky\Templating\TwigEngine');
	}

	/** @test */
	public function extendLayout()
	{
		$twig = $this->makeEngine();
		$result = $twig->render(new Template('template.twig'));
		$this->assertEquals('OK', $result);
	}

	/** @test */
	public function urlGeneration()
	{
		$twig = $this->makeEngine(['Autarky\Routing\RoutingServiceProvider']);
		$this->app->getRequestStack()->push(Request::create('/'));
		$this->app->getRouter()
			->addRoute('GET', '/test/route/{param}', function() {}, 'test.route');
		$result = $twig->render(new Template('urlgeneration.twig'));
		$this->assertEquals('//localhost/test/route/param1', $result);
	}

	/** @test */
	public function partial()
	{
		$twig = $this->makeEngine();
		$mock = m::mock(['bar' => 'baz']);
		$this->app->share('foo', $mock);
		$result = $twig->render(new Template('partial.twig'));
		$this->assertEquals('baz', $result);
	}

	/** @test */
	public function assetUrl()
	{
		$twig = $this->makeEngine(['Autarky\Routing\RoutingServiceProvider']);
		$this->app->getRequestStack()->push(Request::create('/index.php/foo/bar'));
		$result = $twig->render(new Template('asseturl.twig'));
		$this->assertEquals('//localhost/asset/test.css.js', $result);
	}

	/** @test */
	public function sessionMessages()
	{
		$twig = $this->makeEngine(['Autarky\Session\SessionServiceProvider']);
		$session = $this->app->resolve('Symfony\Component\HttpFoundation\Session\Session');
		$data = ['new' => ['_messages' => ['foo', 'bar']]];
		$session->getFlashBag()->initialize($data);
		$result = $twig->render(new Template('sessionmsg.twig'));
		$this->assertEquals("foo\nbar\n", $result);
	}

	/** @test */
	public function namespacedTemplate()
	{
		$twig = $this->makeEngine();
		$twig->addNamespace('foo', TESTS_RSC_DIR.'/templates/namespace');
		$result = $twig->render(new Template('foo:template.twig'));
		$this->assertEquals('OK', $result);
	}
}
