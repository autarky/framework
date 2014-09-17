<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Kernel;

use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Abstract class for service providers.
 *
 * Service providers are modular application configuration classes. They can do
 * anything from binding a service class onto the IoC container to add a bunch
 * of routes with distinct functionality.
 */
abstract class ServiceProvider
{
	/**
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	public function setApplication(Application $app)
	{
		$this->app = $app;
	}

	abstract public function register();

	public function registerConsole(ConsoleApplication $console) {}
}
