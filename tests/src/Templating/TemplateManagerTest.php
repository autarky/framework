<?php
namespace Autarky\Tests\Templating;

use Autarky\Tests\TestCase;
use Autarky\Templating\TemplateManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Mockery as m;

class TemplateManagerTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function mockEngine()
	{
		$mock = m::mock('Autarky\Templating\TemplatingEngineInterface');
		$mock->shouldReceive('render')->andReturnUsing(function($template) {
			return $template;
		})->byDefault();
		return $mock;
	}

	public function makeManager($engine = null)
	{
		return new TemplateManager($engine ?: $this->mockEngine());
	}

	/** @test */
	public function creatingAndRenderingListenersAreCalled()
	{
		$manager = $this->makeManager();
		$manager->setEventDispatcher($dispatcher = new EventDispatcher);
		$dispatcher->addListener('autarky.template.creating: name', function($event) {
			$event->getContext()->foo = 'bar';
		});
		$dispatcher->addListener('autarky.template.rendering: name', function($event) {
			$event->getContext()->foo .= 'baz';
		});
		$template = $manager->render('name', []);
		$this->assertEquals('barbaz', $template->getContext()->foo);
	}
}
