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
 * Array utility functions.
 */
class ArrayUtils
{
	/**
	 * Get an element from an array.
	 *
	 * @param  array  $data
	 * @param  string $key     Specify a nested element by separating keys with full stops.
	 * @param  mixed  $default If the element is not found, return this.
	 *
	 * @return mixed
	 */
	public static function get(array $data, $key, $default = null)
	{
		if ($key === null) {
			return $data;
		}

		foreach (explode('.', $key) as $segment) {
			if (!array_key_exists($segment, $data)) {
				return $default;
			}

			if (!is_array($data)) {
				return $default;
			}

			$data = $data[$segment];
		}

		return $data;
	}

	/**
	 * Determine if an array has a given key.
	 *
	 * @param  array   $data
	 * @param  string  $key
	 *
	 * @return boolean
	 */
	public static function has(array $data, $key)
	{
		foreach (explode('.', $key) as $segment) {
			if (!array_key_exists($segment, $data)) {
				return false;
			}

			if (!is_array($data)) {
				return false;
			}

			$data = $data[$segment];
		}

		return true;
	}

	/**
	 * Set an element of an array.
	 *
	 * @param array  $data
	 * @param string $key   Specify a nested element by separating keys with full stops.
	 * @param mixed  $value
	 */
	public static function set(array &$data, $key, $value)
	{
		$segments = explode('.', $key);

		$key = array_pop($segments);

		// iterate through all of $segments except the last one
		foreach ($segments as $segment) {
			if (!array_key_exists($segment, $data)) {
				$data[$segment] = [];
			} else if (!is_array($data[$segment])) {
				throw new \InvalidArgumentException('Non-array segment encountered');
			}

			$data =& $data[$segment];
		}

		$data[$key] = $value;
	}
}
