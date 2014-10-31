<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Testing;

use PHPUnit_Framework_TestCase;

/**
 * Abstract test case that makes for easy functional testing of your application
 * at the controller level.
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * The application instance.
	 *
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	/**
	 * The browserkit client instance.
	 *
	 * @var \Autarky\Testing\Client
	 */
	protected $client;

	/**
	 * {@inheritdoc}
	 */
	public function setUp()
	{
		$this->app = $this->createApplication();
		$this->app->setEnvironment('testing');
		$this->app->boot();
		$this->app->getErrorHandler()->setRethrow(true);
		$this->client = $this->createClient();
	}

	/**
	 * Enable exception handling in the application being tested.
	 *
	 * By default, uncaught exceptions in the application will simply be thrown
	 * again, meaning you have to call `setExpectedException` or similar if an
	 * exception is expected behaviour. If instead you want the application
	 * error handler to do its job and return a response, call this method.
	 *
	 * @return void
	 */
	protected function enableExceptionHandling()
	{
		$this->app->getErrorHandler()->setRethrow(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function tearDown()
	{
		$this->client = null;
		$this->app = null;
	}

	/**
	 * Create and return the application instance.
	 *
	 * Usually this will simply be a require of your app/start.php file. Make
	 * sure that this file does return $app; at the end.
	 *
	 * @return \Autarky\Kernel\Application
	 */
	abstract protected function createApplication();

	/**
	 * Create a httpkernel\browserkit client.
	 *
	 * Override this method if you want to provide custom parameters to the
	 * client like fake $_SERVER data, browser history or cookies.
	 *
	 * @return \Symfony\Component\HttpKernel\Client
	 */
	protected function createClient()
	{
		return new Client($this->app);
	}
}
