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

/**
 * Interface for config loaders.
 */
interface LoaderInterface
{
	/**
	 * Load a config resource.
	 *
	 * @param  string $location
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException If location does not contain valid data
	 */
	public function load($location);
}
