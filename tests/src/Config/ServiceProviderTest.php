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
		$yamlLoader = $dic->resolve('Autarky\Config\Loaders\CachingYamlFileLoader');
		$this->assertSame($yamlLoader, $dic->resolve('Autarky\Config\Loaders\CachingYamlFileLoader'));
	}

	/** @test */
	public function yamlCachePathIsPassedAndFilesAreCached()
	{
		$configDir = TESTS_RSC_DIR.'/cached-config';
		$cacheDir = $configDir.'/storage/yaml';
		$this->clearCacheDir($configDir);
		$app = new Application('testing', [
			new \Autarky\Container\ContainerProvider(),
			new \Autarky\Config\ConfigProvider($configDir)
		]);
		$app->setErrorHandler(new \Autarky\Errors\StubErrorHandler);
		$app->boot();
		$data = $app->getConfig()->get('testfile');
		$configPath = $configDir.'/testfile.yml';
		$cachePath = $cacheDir.'/'.md5($configPath);
		$this->assertTrue(file_exists($configPath));
		$this->assertTrue(file_exists($cachePath));
		$this->assertEquals($data, require $cachePath);
		$this->clearCacheDir($configDir);
	}

	protected function clearCacheDir($configDir)
	{
		$cacheDir = $configDir.'/storage/yaml';
		if (!is_dir($cacheDir)) {
			$this->fail('Config cache directory does not exist: '.$cacheDir);
		}

		foreach (glob($cacheDir.'/*') as $path) {
			unlink($path);
		}
	}
}
