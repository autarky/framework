<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Config;

use Autarky\Kernel\ServiceProvider;

/**
 * Provides config to the application.
 *
 * This service provider is vital to the framework.
 */
class ConfigProvider extends ServiceProvider
{
	protected $configPath;

	public function __construct($configPath)
	{
		$this->configPath = $configPath;
	}

	public function register()
	{
		$dic = $this->app->getContainer();

		$loaderFactory = new LoaderFactory($dic);
		$dic->instance('Autarky\Config\Loaders\LoaderFactory', $loaderFactory);

		$loaderFactory->addLoader('php', 'Autarky\Config\Loaders\PhpFileLoader');
		$loaderFactory->addLoader(['yml', 'yaml'], 'Autarky\Config\Loaders\YamlFileLoader');

		$store = new FileStore($loaderFactory, $this->configPath, $this->app->getEnvironment());
		$dic->instance('Autarky\Config\FileStore', $store);
		$dic->alias('Autarky\Config\FileStore', 'Autarky\Config\ConfigInterface');

		$this->app->setConfig($store);
	}
}
