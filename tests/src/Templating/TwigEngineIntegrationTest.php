<?php
namespace Autarky\Tests\Templating;

use Mockery as m;
use Autarky\Tests\TestCase;
use Autarky\Templating\TwigEngine;
use Autarky\Templating\Template;
use Symfony\Component\HttpFoundation\Request;

class TwigEngineIntegrationTest extends TestCase
{
	protected function makeApplication($providers = array(), $env = 'testing')
	{
		$providers[] = 'Autarky\Events\EventDispatcherProvider';
		$providers[] = 'Autarky\Templating\TwigTemplatingProvider';
		$app = parent::makeApplication((array) $providers, $env);
		$app->getConfig()->set('path.templates', TESTS_RSC_DIR.'/templates');
		$app->getConfig()->set('path.templates-cache', TESTS_RSC_DIR.'/template-cache');
		$app->getConfig()->set('session.driver', 'null');
		$app->getConfig()->set('session.mock', true);
		$app->getConfig()->set('app.debug', true);
		return $app;
	}

	protected function makeEngine(array $providers = array())
	{
		$this->app = $this->makeApplication($providers);
		$this->app->boot();
		return $this->app->resolve('Autarky\Templating\TemplatingEngine');
	}

	/** @test */
	public function extendLayoutWorks()
	{
		$eng = $this->makeEngine();
		$result = $eng->render('template.twig');
		$this->assertEquals('OK', $result);
	}

	/** @test */
	public function urlGenerationViaUrlFunctionWorks()
	{
		$eng = $this->makeEngine(['Autarky\Routing\RoutingProvider']);
		$this->app->getRequestStack()->push(Request::create('/'));
		$this->app->getRouter()
			->addRoute('GET', '/test/route/{param}', function() {}, 'test.route');
		$result = $eng->render('urlgeneration.twig');
		$this->assertEquals('//localhost/test/route/param1', $result);
	}

	/** @test */
	public function partialFunctionWorks()
	{
		$eng = $this->makeEngine();
		$mock = m::mock(['bar' => 'baz']);
		$this->app->getContainer()->instance('foo', $mock);
		$result = $eng->render('partial.twig');
		$this->assertEquals('baz', $result);
	}

	/** @test */
	public function assetUrlGenerationWorksViaAssetFunction()
	{
		$eng = $this->makeEngine(['Autarky\Routing\RoutingProvider']);
		$this->app->getRequestStack()->push(Request::create('/index.php/foo/bar'));
		$result = $eng->render('asseturl.twig');
		$this->assertEquals('//localhost/asset/test.css.js', $result);
	}

	/** @test */
	public function sessionMessagesAreAvailableAsGlobals()
	{
		$eng = $this->makeEngine(['Autarky\Session\SessionProvider']);
		$session = $this->app->resolve('Symfony\Component\HttpFoundation\Session\Session');
		$data = ['new' => ['_messages' => ['foo', 'bar']]];
		$session->getFlashBag()->initialize($data);
		$result = $eng->render('sessionmsg.twig');
		$this->assertEquals("foo\nbar\n", $result);
	}

	/** @test */
	public function namespacedTemplatesCanBeRendered()
	{
		$eng = $this->makeEngine();
		$eng->addNamespace('namespace', TESTS_RSC_DIR.'/templates/vendor/namespace');
		$result = $eng->render('namespace:template1.twig');
		$this->assertEquals('OK', $result);
	}

	/** @test */
	public function namespacedTemplatesCanBeOverridden()
	{
		$eng = $this->makeEngine();
		$eng->addNamespace('namespace', TESTS_RSC_DIR.'/templates/vendor/namespace');
		$result = $eng->render('namespace:template2.twig');
		$this->assertEquals('Overridden', $result);
	}

	/** @test */
	public function eventsAreFiredWhenTemplatesAreCreatedAndRendered()
	{
		$eng = $this->makeEngine();
		$eng->setEventDispatcher($dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher);
		$events = [];
		$callback = function($event) use(&$events) { $events[] = $event->getName(); };
		$eng->creating('template', $callback);
		$eng->rendering('template', $callback);
		$eng->creating('layout', $callback);
		$eng->rendering('layout', $callback);
		$expected = [
			'template.creating: template',
			'template.creating: layout',
			'template.rendering: template',
			'template.rendering: layout',
		];
		$eng->render('template.twig');
		$this->assertEquals($expected, $events);
	}
}
