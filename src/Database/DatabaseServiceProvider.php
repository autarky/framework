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
use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider that binds a shared PDO instance onto the container,
 * using settings found in the database config file.
 */
class DatabaseServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->getContainer()->share('PDO', function ($container) {
			$dsn = $this->app->getConfig()->get('database.dsn');
			$username = $this->app->getConfig()->get('database.username');
			$password = $this->app->getConfig()->get('database.password');
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_STRINGIFY_FETCHES => false,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
			];

			return new PDO($dsn, $username, $password, $options);
		});
	}
}
