<?php
namespace Autarky\Tests\Templating;

use Mockery as m;
use Autarky\Tests\TestCase;
use Autarky\Templating\TwigEngine;
use Symfony\Component\HttpFoundation\Request;

class TwigEngineTest extends TestCase
{
	protected function makeApplication(array $providers = array())
	{
		$app = parent::makeApplication();
		$app->getConfig()->set('path.templates', TESTS_RSC_DIR.'/templates');
		$app->getConfig()->set('path.templates-cache', TESTS_RSC_DIR.'/template-cache');
		$app->getConfig()->set('session.mock', true);
		$app->getConfig()->set('app.debug', true);
		$app->getConfig()->set('app.providers', $providers);
		return $app;
	}

	public function makeTwigEngine(array $providers = array())
	{
		$this->app = $this->makeApplication($providers);
		$this->app->boot();
		return new TwigEngine($this->app);
	}

	/** @test */
	public function extendLayout()
	{
		$twig = $this->makeTwigEngine();
		$result = $twig->render('template.twig');
		$this->assertEquals('OK', $result);
	}

	/** @test */
	public function urlGeneration()
	{
		$twig = $this->makeTwigEngine(['Autarky\Routing\RoutingServiceProvider']);
		$this->app->getRouter()->setCurrentRequest(Request::create('/'));
		$this->app->getRouter()
			->addRoute('GET', '/test/route/{param}', function() {}, 'test.route');
		$result = $twig->render('urlgeneration.twig');
		$this->assertEquals('//localhost/test/route/param1', $result);
	}

	/** @test */
	public function partial()
	{
		$twig = $this->makeTwigEngine();
		$mock = m::mock(['bar' => 'baz']);
		$this->app->share('foo', $mock);
		$result = $twig->render('partial.twig');
		$this->assertEquals('baz', $result);
	}

	/** @test */
	public function assetUrl()
	{
		$twig = $this->makeTwigEngine(['Autarky\Routing\RoutingServiceProvider']);
		$this->app->getRouter()->setCurrentRequest(Request::create('/index.php/foo/bar'));
		$result = $twig->render('asseturl.twig');
		$this->assertEquals('//localhost/asset/test.css.js', $result);
	}

	/** @test */
	public function sessionMessages()
	{
		$app = $this->makeApplication(['Autarky\Session\SessionServiceProvider']);
		$app->boot();
		$session = $app->resolve('Symfony\Component\HttpFoundation\Session\Session');
		$data = ['new' => ['_messages' => ['foo', 'bar']]];
		$session->getFlashBag()->initialize($data);
		$twig = new TwigEngine($app);
		$result = $twig->render('sessionmsg.twig');
		$this->assertEquals("foo\nbar\n", $result);
	}

	/** @test */
	public function namespacedTemplate()
	{
		$twig = $this->makeTwigEngine();
		$twig->addNamespace('foo', TESTS_RSC_DIR.'/templates/namespace');
		$result = $twig->render('foo:template.twig');
		$this->assertEquals('OK', $result);
	}
}
