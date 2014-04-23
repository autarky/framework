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

use Autarky\Kernel\Application;

/**
 * Abstract test case that makes for easy functional testing of your application
 * at the controller level.
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * {@inheritdoc}
	 */
	public function setUp()
	{
		$this->app = $this->createApplication();
		$this->client = $this->createClient();
		$this->app->setEnvironment('testing');
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
	 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
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
