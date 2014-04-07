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

/**
 * Config store using .php files. Said .php files should return an array of
 * config values. Example:
 *
 * config/foo.php
 * return ['bar' => 'baz'];
 * PhpFileStore::get('foo.bar') == 'baz'
 */
class PhpFileStore implements ConfigInterface
{
	use NamespacedResourceResolver;

	protected $data = [];

	/**
	 * @param string $path Path to config files in the global namespace.
	 */
	public function __construct($path)
	{
		$this->setLocation($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null)
	{
		list($namespace, $group, $key) = $this->parseKey($key);

		return $this->getFromNamespace($namespace, $group, $key);
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
		
		return Arr::set($this->data, $fullKey, $value);
	}

	/**
	 * Add a namespace to the config loader.
	 *
	 * @param string $namespace
	 * @param string $location  Path to config files.
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
		$data = [];

		// iterate through possible locations and merge the data array.
		// locations are sorted so that overrrides come last.
		foreach ($locations as $location) {
			$path = $location .'/'. $group .'.php';
			if (file_exists($path)) {
				$fileData = require $path;
				if (!is_array($fileData)) {
					throw new \InvalidArgumentException("Config file $path must return an array");
				}
				$data = array_merge($data, $fileData);
			}
		}

		$this->data[$dataKey] = $data;
	}
}
