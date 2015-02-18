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

use Symfony\Component\Yaml\Parser;

use Autarky\Provider;
use Autarky\Config\Loaders\YamlFileLoader;
use Autarky\Config\Loaders\CachingYamlFileLoader;

/**
 * Provides config to the application.
 *
 * This service provider is vital to the framework.
 */
class ConfigProvider extends Provider
{
	/**
	 * The path in which config files are located.
	 *
	 * @var string
	 */
	protected $configPath;

	/**
	 * @param string $configPath The path in which config files are located.
	 */
	public function __construct($configPath)
	{
		$this->configPath = $configPath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->app->setConfig($store = $this->getConfigStore());

		if ($store->has('app.configurators')) {
			foreach ($store->get('app.configurators') as $configurator) {
				$this->app->config($configurator);
			}
		}
	}

	protected function getConfigStore()
	{
		$dic = $this->app->getContainer();

		// set up the factory
		$loaderFactory = new LoaderFactory($dic);
		$dic->instance('Autarky\Config\Loaders\LoaderFactory', $loaderFactory);

		// set up the config store
		$store = new FileStore($loaderFactory, $this->configPath, $this->app->getEnvironment());
		$dic->instance('Autarky\Config\FileStore', $store);
		$dic->alias('Autarky\Config\FileStore', 'Autarky\Config\ConfigInterface');

		// set up the PHP file loader
		$dic->share('Autarky\Config\Loaders\PhpFileLoader');
		$loaderFactory->addLoader('php', 'Autarky\Config\Loaders\PhpFileLoader');

		// set up the YAML config file loader
		$dic->define('Autarky\Config\Loaders\CachingYamlFileLoader', function($dic) {
			return new CachingYamlFileLoader(
				$dic->resolve('Symfony\Component\Yaml\Parser'),
				$this->getYamlCachePath()
			);
		});
		$dic->share('Autarky\Config\Loaders\CachingYamlFileLoader');
		$loaderFactory->addLoader(['yml', 'yaml'], 'Autarky\Config\Loaders\CachingYamlFileLoader');

		// done!
		return $store;
	}

	protected function getYamlCachePath()
	{
		if (file_exists($this->configPath.'/path.yml')) {
			throw new \RuntimeException("The 'path' config file cannot be YAML when using the caching YAML config file loader.");
		}

		$config = $this->app->getConfig();

		$path = $config->get('path.storage').'/yaml';

		if (is_dir($path) && is_writable($path)) {
			return $path;
		}

		return null;
	}
}
