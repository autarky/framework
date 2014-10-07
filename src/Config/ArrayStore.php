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

use Autarky\Support\ArrayUtils;

/**
 * Simple array-based store primarily for testing purposes.
 */
class ArrayStore implements ConfigInterface
{
	protected $data = [];

	public function __construct(array $data = array())
	{
		$this->data = $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null)
	{
		return ArrayUtils::get($this->data, $key, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		ArrayUtils::set($this->data, $key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addNamespace($namespace, $location)
	{
		// do nothing
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEnvironment($environment)
	{
		// do nothing
	}
}
