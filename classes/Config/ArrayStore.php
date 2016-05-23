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

use Autarky\Utils\ArrayUtil;

/**
 * Simple array-based store primarily for testing purposes.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class ArrayStore implements ConfigInterface
{
	protected $data = [];

	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return ArrayUtil::has($this->data, $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null)
	{
		return ArrayUtil::get($this->data, $key, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		ArrayUtil::set($this->data, $key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function mount($namespace, $location)
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
