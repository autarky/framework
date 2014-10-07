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
		$data = require $path;

		if (!is_array($data)) {
			throw new \RuntimeException("Config file \"$path\" must return an array");
		}

		return $data;
	}
}
