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

/**
 * Interface for configuration stores.
 */
interface ConfigInterface
{
	/**
	 * Determine if a key exists in the store.
	 *
	 * Should return true even if the value is null.
	 *
	 * @param  string  $key
	 *
	 * @return boolean
	 */
	public function has($key);

	/**
	 * Get an item from the store.
	 *
	 * @param  string $key
	 * @param  mixed  $default If the value is not found, return this instead.
	 *
	 * @return mixed
	 */
	public function get($key, $default = null);

	/**
	 * Set an item in the store temporarily - more specifically, the lifetime of
	 * the store object.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set($key, $value);

	/**
	 * Mount a path to a specific location in the config tree.
	 *
	 * @param  string $location
	 * @param  string $path
	 *
	 * @return void
	 */
	public function mount($location, $path);

	/**
	 * Set the environment.
	 *
	 * @param string $environment
	 */
	public function setEnvironment($environment);
}
