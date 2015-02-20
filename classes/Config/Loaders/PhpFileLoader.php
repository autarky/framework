<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Config\Loaders;

use Autarky\Config\LoadException;
use Autarky\Config\LoaderInterface;

/**
 * PHP file config loader.
 *
 * Works with PHP files which declare a file-wide "return" statement which
 * returns an associative array.
 */
class PhpFileLoader implements LoaderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function load($path)
	{
		$data = static::requireFile($path);

		if (!is_array($data)) {
			throw new LoadException("Config file \"$path\" must return an array");
		}

		return $data;
	}

	/**
	 * Require a file and return the result.
	 *
	 * Static method to make it impossible to reference $this in the file.
	 *
	 * @param  string     $_path
	 *
	 * @return mixed
	 */
	protected static function requireFile($_path)
	{
		return require $_path;
	}
}
