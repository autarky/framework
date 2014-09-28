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

class ConfigProvider extends ServiceProvider
{
	public function __construct($configPath)
	{
		$this->configPath = $configPath;
	}

	public function register()
	{
		$dic = $this->app->getContainer();

		$loaderFactory = new LoaderFactory($dic);
		$loaderFactory->addLoader('php', 'Autarky\Config\Loaders\PhpFileLoader');
		$dic->instance('Autarky\Config\Loaders\LoaderFactory', $loaderFactory);

		$this->app->setConfig($store = new FileStore($loaderFactory, $this->configPath));
		$dic->instance('Autarky\Config\FileStore', $store);
		$dic->alias('Autarky\Config\FileStore', 'Autarky\Config\ConfigInterface');
	}
}
