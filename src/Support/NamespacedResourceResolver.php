<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Support;

/**
 * Trait for namespaced resource resolvers. Provides shared functionality.
 */
trait NamespacedResourceResolver
{
	/**
	 * The default location.
	 *
	 * @var string
	 */
	protected $location;

	/**
	 * Namespace locations. Associative array of namespace => [location, ...].
	 * One namespace can have multiple locations. Locations are usually
	 * directories containing files (config files, templates....
	 *
	 * @var array
	 */
	protected $locations = [];

	/**
	 * The environment.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * @param string $location  The default location,
	 */
	public function setLocation($location)
	{
		$this->location = $location;
	}

	/**
	 * Set the enrivonment.
	 *
	 * @param string $environment
	 */
	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}

	/**
	 * Parse a key, returning an array of [namespace, group, key]. If namespace
	 * is null, no namespace is provided. If key is null, the entire group
	 * is being requested.
	 *
	 * @param  string $key
	 *
	 * @return array
	 */
	protected function parseKey($key)
	{
		if (strpos($key, ':') !== false) {
			list($namespace, $key) = explode(':', $key);
		} else {
			$namespace = null;
		}

		$dotPos = strpos($key, '.');

		if ($dotPos !== false) {
			$group = substr($key, 0, $dotPos);
			$key = substr($key, $dotPos+1);
		} else {
			$group = $key;
			$key = null;
		}

		return [$namespace, $group, $key];
	}

	/**
	 * Get the possible locations given a namespace.
	 *
	 * @param  string $namespace Leave out to search the global namespace.
	 *
	 * @return array
	 */
	protected function getLocations($namespace = null)
	{
		if ($namespace === null) {
			return $this->getDefaultLocations();
		}

		if (!array_key_exists($namespace, $this->locations)) {
			throw new \InvalidArgumentException("No locations registered for $namespace");
		}

		$overrides = array_map(function($path) use($namespace) {
			return $path .'/'. $namespace;
		}, $this->getDefaultLocations());

		return $this->locations[$namespace] + $overrides;
	}

	/**
	 * Get the locations available for the global namespace.
	 *
	 * @return array
	 */
	protected function getDefaultLocations()
	{
		return $this->environment === null ? [$this->location] :
			[$this->location, $this->location.'/'.$this->environment];
	}
}
