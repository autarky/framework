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

use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider that binds a shared PDO instance onto the container,
 * using settings found in the database config file.
 */
class DatabaseServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->getContainer()->share('Autarky\Database\MultiPdoContainer', function($container) {
			return new MultiPdoContainer($this->app->getConfig());
		});

		$this->app->getContainer()->share('PDO', function($container) {
			return $container->resolve('Autarky\Database\MultiPdoContainer')->getPdo();
		});
	}
}
