<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing;

use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider for the FastRoute implementation.
 */
class RoutingServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->setRouter(new Router($this->app->getContainer()));
	}
}
