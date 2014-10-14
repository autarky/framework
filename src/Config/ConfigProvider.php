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
use Autarky\Config\Loaders\YamlFileLoader;
use Autarky\Config\Loaders\CachingYamlFileLoader;
use Symfony\Component\Yaml\Parser;

/**
 * Provides config to the application.
 *
 * This service provider is vital to the framework.
 */
class ConfigProvider extends ServiceProvider
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
		$dic = $this->app->getContainer();

		$loaderFactory = new LoaderFactory($dic);
		$dic->instance('Autarky\Config\Loaders\LoaderFactory', $loaderFactory);

		$store = new FileStore($loaderFactory, $this->configPath, $this->app->getEnvironment());
		$dic->instance('Autarky\Config\FileStore', $store);
		$dic->alias('Autarky\Config\FileStore', 'Autarky\Config\ConfigInterface');

		$loaderFactory->addLoader('php', 'Autarky\Config\Loaders\PhpFileLoader');

		$dic->define('Autarky\Config\Loaders\CachingYamlFileLoader', function() {
			return new CachingYamlFileLoader(new YamlFileLoader(new Parser), $this->getYamlCachePath());
		});
		$dic->share('Autarky\Config\Loaders\CachingYamlFileLoader');
		$loaderFactory->addLoader(['yml', 'yaml'], 'Autarky\Config\Loaders\CachingYamlFileLoader');

		$this->app->setConfig($store);
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
