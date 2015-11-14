<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Http;

use Autarky\Providers\AbstractProvider;

class CookieProvider extends AbstractProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$cookieQueue = new CookieQueue;
		$this->app->getContainer()->instance('Autarky\Http\CookieQueue', $cookieQueue);
		$this->app->addMiddleware(['Autarky\Http\CookieMiddleware', $cookieQueue]);
	}
}
