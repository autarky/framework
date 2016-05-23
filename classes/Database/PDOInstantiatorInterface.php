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

/**
 * PDO instantiator.
 *
 * @internal
 */
interface PDOInstantiatorInterface
{
	/**
	 * Instantiate a PDO instance.
	 *
	 * @param  string $dsn
	 * @param  string $username
	 * @param  string $password
	 * @param  array  $options
	 *
	 * @return \PDO
	 */
	public function instantiate($dsn, $username = null, $password = null, array $options = []);
}
