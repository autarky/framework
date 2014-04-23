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

use Autarky\Support\Arr;

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

	public function get($key, $default = null)
	{
		return Arr::get($this->data, $key, $default);
	}

	public function set($key, $value)
	{
		Arr::set($this->data, $key, $value);
	}

	public function addNamespace($namespace, $location)
	{
		// do nothing
	}

	public function setEnvironment($environment)
	{
		// do nothing
	}
}
