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
use Symfony\Component\HttpKernel\Client;

use Autarky\Kernel\Application;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->app = $this->createApplication();
		$this->client = $this->createClient();
		$this->app->setEnvironment('testing');
		$this->app->boot();
	}

	public function tearDown()
	{
		$this->client = null;
		$this->app = null;
	}

	abstract protected function createApplication();

	protected function createClient()
	{
		return new Client($this->app, []);
	}
}
