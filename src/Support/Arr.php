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
class Arr
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
				return $defatult;
			}

			$data = $data[$segment];
		}

		return $data;
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

		// iterate through all of $segments except the last one
		foreach (array_slice($segments, 0, -1) as $segment) {
			if (!array_key_exists($segment, $data)) {
				$data[$segment] = [];
			}
		}

		// get the last element of $segments
		$segment = array_slice($segments, -1, 1);

		$data[$segment] = $value;
	}
}
