<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Database;

interface ConnectionFactoryInterface
{
	/**
	 * Create a new PDO instance.
	 *
	 * @param  array  $config
	 *
	 * @return \PDO
	 *
	 * @throws \InvalidArgumentException If connection is incorrectly configured
	 * @throws CannotConnectException If construction of PDO object fails
	 */
	public function makePdo(array $config);
}
