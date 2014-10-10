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

use Autarky\Support\NamespacedResourceResolver;
use Autarky\Support\ArrayUtils;

/**
 * File-based config implementation.
 *
 * Reads files from one or multiple directories, with the possibility of
 * cascading for different environments and overriding of namespaces.
 */
class FileStore implements ConfigInterface
{
	use NamespacedResourceResolver;

	/**
	 * The loader factory instance.
	 *
	 * @var LoaderFactory
	 */
	protected $loaderFactory;

	/**
	 * The loaded config data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @param LoaderFactory $loaderFactory
	 * @param string        $path          Path to config files in the global namespace.
	 * @param string        $environment
	 */
	public function __construct(LoaderFactory $loaderFactory, $path, $environment)
	{
		$this->loaderFactory = $loaderFactory;
		$this->environment = $environment;
		$this->setLocation($path);
	}

	/**
	 * Get the loader factory instance.
	 *
	 * @return LoaderFactory
	 */
	public function getLoaderFactory()
	{
		return $this->loaderFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null)
	{
		list($namespace, $group, $key) = $this->parseKey($key);

		return $this->getFromNamespace($namespace, $group, $key, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($fullKey, $value)
	{
		list($namespace, $group, $key) = $this->parseKey($fullKey);

		$dataKey = $namespace === null ? $group : $namespace.':'.$group;

		if (!array_key_exists($dataKey, $this->data)) {
			$this->loadData($namespace, $group, $dataKey);
		}

		ArrayUtils::set($this->data, $fullKey, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addNamespace($namespace, $location)
	{
		if (!array_key_exists($namespace, $this->locations)) {
			$this->locations[$namespace] = [];
		}

		$this->locations[$namespace][] = $location;
	}

	protected function getFromNamespace($namespace, $group, $key = null, $default = null)
	{
		$dataKey = $namespace === null ? $group : $namespace.':'.$group;

		if (!array_key_exists($dataKey, $this->data)) {
			$this->loadData($namespace, $group, $dataKey);
		}

		$dataKey = $key === null ? $dataKey : $dataKey.'.'.$key;

		return ArrayUtils::get($this->data, $dataKey, $default);
	}

	protected function loadData($namespace, $group, $dataKey)
	{
		$locations = $this->getLocations($namespace);
		$extensions = $this->loaderFactory->getExtensions();
		$data = [];

		// iterate through possible locations and merge the data array.
		// locations are sorted so that overrrides come last.
		foreach ($locations as $location) {
			foreach ($extensions as $extension) {
				$path = "{$location}/{$group}.{$extension}";

				if (!file_exists($path)) {
					continue;
				}

				if (!is_readable($path)) {
					throw new \RuntimeException("File is not readable: $path");
				}

				$fileData = $this->getDataFromFile($path);
				$data = array_replace($data, $fileData);
				break;
			}
		}

		$this->data[$dataKey] = $data;
	}

	protected function getDataFromFile($path)
	{
		$loader = $this->loaderFactory->getForPath($path);

		return $loader->load($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEnvironment($environment)
	{
		// do nothing - deprecated method
	}
}
