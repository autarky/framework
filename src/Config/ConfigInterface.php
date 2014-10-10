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

use Autarky\Support\NamespacedResourceResolverInterface;

/**
 * Interface for configuration stores.
 */
interface ConfigInterface extends NamespacedResourceResolverInterface
{
	/**
	 * Determine if a key exists in the store.
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
	 */
	public function set($key, $value);

	/**
	 * Set the environment.
	 *
	 * @param string $environment
	 *
	 * @deprecated
	 */
	public function setEnvironment($environment);
}
