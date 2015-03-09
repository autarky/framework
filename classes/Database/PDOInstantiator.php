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

use PDO;

/**
 * PDO instantiator.
 *
 * @internal
 */
class PDOInstantiator
{
	public function instantiate($dsn, $username = null, $password = null, array $options = array())
	{
		return new PDO($dsn, $username, $password, $options);
	}
}
