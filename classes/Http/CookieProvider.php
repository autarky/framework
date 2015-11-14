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

class SessionProvider extends AbstractProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->dic->share('Autarky\Http\CookieQueue');
		$this->app->addMiddleware(['Autarky\Http\CookieMiddleware', $this->app]);
	}
}
