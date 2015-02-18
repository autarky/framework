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

use Autarky\Container\ContainerInterface;
use Autarky\Provider;

/**
 * Simple service provider that binds a shared PDO instance onto the container,
 * using settings found in the database config file.
 */
class DatabaseProvider extends Provider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->define('Autarky\Database\ConnectionManager', function() {
			return new ConnectionManager($this->app->getConfig());
		});
		$dic->share('Autarky\Database\ConnectionManager');

		$factory = $dic->makeFactory(['Autarky\Database\ConnectionManager', 'getPdo']);
		$factory->addScalarArgument('$connection', 'string', false, null);
		$dic->define('PDO', $factory);
	}
}
