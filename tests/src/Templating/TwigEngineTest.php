<?php
namespace Autarky\Tests\Templating;

use Mockery as m;

use Autarky\Tests\TestCase;
use Autarky\Templating\TwigEngine;
use Autarky\Kernel\Application;
use Autarky\Container\Container;
use Autarky\Config\ArrayStore;

class TwigEngineTest extends TestCase
{
	/**
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	/**
	 * @var \Autarky\Templating\TwigEngine|\Mockery\Mock
	 */
	protected $twig;

	public function tearDown()
	{
		m::close();
	}

	protected function makeEngine()
	{
		$this->app = $this->makeApplication();
		$this->twig = m::mock('Twig_Environment');
		$this->twig->shouldReceive('addExtension');
		return new TwigEngine($this->app, $this->twig);
	}

	protected function engineShouldReceiveRenderWith($name, array $context)
	{
		$template = m::mock('Twig_TemplateInterface');
		$this->twig->shouldReceive('loadTemplate')->once()->with($name)->andReturn($template);
		return $template->shouldReceive('render')->once()->with($context);
	}

	/** @test */
	public function simpleRender()
	{
		$engine = $this->makeEngine();
		$this->engineShouldReceiveRenderWith('foo.html', ['foo' => 'bar'])->andReturn('html');
		$this->assertEquals('html', $engine->render('foo.html', ['foo' => 'bar']));
	}

	/** @test */
	public function contextHandlersAddDataToContext()
	{
		$engine = $this->makeEngine();
		$engine->registerContextHandler('foo.html', function() { return ['bar' => 'baz']; });
		$this->engineShouldReceiveRenderWith('foo.html', ['foo' => 'bar', 'bar' => 'baz'])->andReturn('html');		
		$this->assertEquals('html', $engine->render('foo.html', ['foo' => 'bar']));
	}
}
