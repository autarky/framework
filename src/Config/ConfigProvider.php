<?php
namespace Autarky\Config;

use Autarky\Kernel\ServiceProvider;

class ConfigProvider extends ServiceProvider
{
	public function __construct($configPath)
	{
		$this->configPath = $configPath;
	}

	public function register()
	{
		$loaderFactory = new \Autarky\Config\LoaderFactory($this->app->getContainer());
		$loaderFactory->addLoader('php', 'Autarky\Config\Loaders\PhpFileLoader');
		$config = new \Autarky\Config\FileStore($loaderFactory, $this->configPath);

		$this->app->setConfig($config);
	}
}
