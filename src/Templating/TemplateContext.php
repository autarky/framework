<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Templating;

class TemplateContext
{
	protected $data = [];

	public function __construct(array $context = null)
	{
		if ($context) {
			$this->data = $context;
		}
	}

	public function &__get($key)
	{
		if (!array_key_exists($key, $this->data)) {
			throw new \OutOfBoundsException("Undefined index for context: $key");
		}

		return $this->data[$key];
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->data);
	}

	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}

	public function __unset($key)
	{
		unset($this->data[$key]);
	}

	public function &offsetGet($key)
	{
		if (!array_key_exists($key, $this->data)) {
			throw new \OutOfBoundsException("Undefined index for context: $key");
		}

		return $this->data[$key];
	}

	public function offsetExists($key)
	{
		return array_key_exists($key, $this->data);
	}

	public function offsetSet($key, $value)
	{
		$this->data[$key] = $value;
	}

	public function offsetUnset($key)
	{
		unset($this->data[$key]);
	}

	public function toArray()
	{
		return $this->data;
	}
}
