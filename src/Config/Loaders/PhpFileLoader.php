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

class PhpFileLoader
{
	public function load($path)
	{
		$data = require $path;

		if (!is_array($data)) {
			throw new \InvalidArgumentException("Config file \"$path\" must return an array");
		}

		return $data;
	}
}
