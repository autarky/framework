<?php
namespace Autarky\Tests\Templating;

use Autarky\Tests\TestCase;
use Mockery as m;

class ServiceProviderTest extends TestCase
{
	protected function assertSingleton($class)
	{
		$app = $this->makeApplication('Autarky\Templating\TwigServiceProvider');
		$app->boot();
		$object = $app->getContainer()->resolve($class);
		$this->assertInstanceOf($class, $object);
		$this->assertSame($object, $app->getContainer()->resolve($class));
	}

	/** @test */
	public function canResolveAndAreSingletons()
	{
		$this->assertSingleton('Autarky\Templating\TemplateManager');
		$this->assertSingleton('Autarky\Templating\TwigEngine');
		$this->assertSingleton('Twig_Environment');
	}

	/** @test */
	public function engineInterfaceCanBeResolved()
	{
		$app = $this->makeApplication('Autarky\Templating\TwigServiceProvider');
		$app->boot();
		$this->assertSame(
			$app->resolve('Autarky\Templating\TwigEngine'),
			$app->resolve('Autarky\Templating\TemplatingEngineInterface')
		);
	}
}
