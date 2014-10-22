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
 * anything from binding a service class onto the service container to add a
 * bunch of routes with distinct functionality.
 */
abstract class ServiceProvider
{
	/**
	 * The application instance.
	 *
	 * @var \Autarky\Kernel\Application
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

	/**
	 * Get the class(es) that must exist for this provider to function.
	 *
	 * @return array
	 */
	public function getClassDependencies()
	{
		return [];
	}

	/**
	 * Get the class(es) that must be bound to the container for this provider
	 * to function.
	 *
	 * @return array
	 */
	public function getContainerDependencies()
	{
		return [];
	}

	/**
	 * Get the provider(s) that must be loaded in order for this one to
	 * function.
	 *
	 * @return array
	 */
	public function getProviderDependencies()
	{
		return [];
	}
}
