<?php
namespace Autarky\Tests\Templating;

use Mockery as m;

use Autarky\Tests\TestCase;
use Autarky\Templating\Template;
use Autarky\Templating\TwigEngine;

class TwigEngineIntegrationTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function makeEngine()
	{
		return new TwigEngine($this->twig = m::mock('Twig_Environment'));
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
		$this->assertEquals('html', $engine->render(new Template('foo.html', ['foo' => 'bar'])));
	}
}
