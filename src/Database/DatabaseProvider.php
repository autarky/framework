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
use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider that binds a shared PDO instance onto the container,
 * using settings found in the database config file.
 */
class DatabaseProvider extends ServiceProvider
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

		$dic->define('PDO', function(ContainerInterface $container) {
			return $container->resolve('Autarky\Database\ConnectionManager')
				->getPdo();
		});
	}
}
