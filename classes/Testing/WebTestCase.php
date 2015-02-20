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

use Symfony\Component\HttpKernel\Client;

abstract class WebTestCase extends TestCase
{
	/**
	 * The browserkit client instance.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * {@inheritdoc}
	 */
	public function setUp()
	{
		parent::setup();
		$this->client = $this->createClient();
	}

	/**
	* Create a httpkernel\browserkit client.
	*
	* Override this method if you want to provide custom parameters to the
	* client like fake $_SERVER data, browser history or cookies.
	*
	* @return Client
	*/
	protected function createClient()
	{
		return new Client($this->app);
	}
}
