<?php
namespace Autarky\Tests\Templating;

use Autarky\Tests\TestCase;
use Mockery as m;

class ServiceProviderTest extends TestCase
{
	protected function makeApplication($providers = array(), $env = 'testing')
	{
		$providers[] = 'Autarky\Templating\TwigTemplatingProvider';
		$app = parent::makeApplication($providers, $env);
		$app->getConfig()->set('path.templates', TESTS_RSC_DIR.'/templates');
		return $app;
	}

	protected function assertSingleton($class)
	{
		$app = $this->makeApplication();
		$app->boot();
		$object = $app->getContainer()->resolve($class);
		$this->assertInstanceOf($class, $object);
		$this->assertSame($object, $app->getContainer()->resolve($class));
	}

	/** @test */
	public function canResolveAndAreSingletons()
	{
		$this->assertSingleton('Autarky\Templating\TemplatingEngine');
		$this->assertSingleton('Autarky\Templating\Twig\Environment');
		$this->assertSingleton('Twig_Environment');
	}

	/** @test */
	public function engineInterfaceCanBeResolved()
	{
		$app = $this->makeApplication();
		$app->boot();
		$this->assertSame(
			$app->resolve('Autarky\Templating\Twig\Environment'),
			$app->resolve('Twig_Environment')
		);
	}

	/** @test */
	public function userExtensionsAreAdded()
	{
		$app = $this->makeApplication();
		$app->getConfig()->set('twig.extensions', [__NAMESPACE__.'\\StubTwigExtension']);
		$app->boot();
		$app->resolve('Twig_Environment');
		$this->assertEquals(true, StubTwigExtension::$initialized);		
	}
}

class StubTwigExtension extends \Twig_Extension
{
	public static $initialized = false;

	public function __construct()
	{
		static::$initialized = true;
	}

	public function getName()
	{
		return 'stub';
	}
}
