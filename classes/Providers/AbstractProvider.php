<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Providers;

use Symfony\Component\Console\Application as ConsoleApplication;
use Autarky\Application;

/**
 * Abstract class for service providers.
 *
 * Service providers are modular application configuration classes. They can do
 * anything from binding a service class onto the service container to add a
 * bunch of routes with distinct functionality.
 */
abstract class AbstractProvider implements ProviderInterface, ConsoleProviderInterface
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
	 * {@inheritdoc}
	 */
	abstract public function register();

	/**
	 * {@inheritdoc}
	 */
	public function registerConsole(ConsoleApplication $console) {}
}
