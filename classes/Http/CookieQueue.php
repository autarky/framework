<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Http;

class CookieQueue
{
	protected $cookies = [];

	public function has($key)
	{
		return array_key_exists($this->cookies, $key);
	}

	public function get($key)
	{
		return $this->cookies[$key];
	}

	public function all()
	{
		return $this->cookies;
	}

	public function set($key, $value)
	{
		$this->cookies[$key] = $value;
	}

	public function remove($key)
	{
		unset($this->cookies[$key]);
	}
}
