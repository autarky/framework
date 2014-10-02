<?php
namespace Autarky\Tests\Config;

use Autarky\Tests\TestCase;
use Autarky\Kernel\Application;

class ServiceProviderTest extends TestCase
{
	protected function makeApplication($providers = array(), $env = 'testing')
	{
		$app = new Application($env, [
			new \Autarky\Container\ContainerProvider(),
			new \Autarky\Config\ConfigProvider(TESTS_RSC_DIR.'/config')
		]);
		$app->setErrorHandler(new \Autarky\Errors\StubErrorHandler);
		$app->boot();
		return $app;
	}

	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication();
		$dic = $app->getContainer();
		$this->assertSame($dic->resolve('Autarky\Config\FileStore'), $dic->resolve('Autarky\Config\ConfigInterface'));
	}
}
