<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky;

use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Abstract class for service providers.
 *
 * Service providers are modular application configuration classes. They can do
 * anything from binding a service class onto the service container to add a
 * bunch of routes with distinct functionality.
 */
abstract class Provider
{
	/**
	 * The application instance.
	 *
	 * @var \Autarky\Application
	 */
	protected $app;

	/**
	 * @param Application $app
	 */
	public function setApplication(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	abstract public function register();

	/**
	 * Register the service provider with the console application.
	 *
	 * @param  ConsoleApplication $console
	 *
	 * @return void
	 */
	public function registerConsole(ConsoleApplication $console) {}
}
