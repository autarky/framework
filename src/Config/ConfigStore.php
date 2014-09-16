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
use Autarky\Support\Arr;

class ConfigStore implements ConfigInterface
{
	use NamespacedResourceResolver;

	protected $loaderFactory;
	protected $data = [];

	/**
	 * @param string $path Path to config files in the global namespace.
	 */
	public function __construct(LoaderFactory $loaderFactory, $path)
	{
		$this->loaderFactory = $loaderFactory;
		$this->setLocation($path);
	}

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

		if (!array_key_exists($group, $this->data)) {
			$this->loadData($namespace, $group, $key);
		}

		$dataKey = $namespace === null ? $group : $namespace .':'. $group;
		
		Arr::set($this->data, $fullKey, $value);
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

		return Arr::get($this->data, $dataKey, $default);
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
				if (file_exists($path)) {
					$fileData = $this->getDataFromFile($path);
					$data = array_merge($data, $fileData);
					break;
				}
			}
		}

		$this->data[$dataKey] = $data;
	}

	public function getDataFromFile($path)
	{
		$loader = $this->loaderFactory->getForPath($path);

		return $loader->load($path);
	}
}
