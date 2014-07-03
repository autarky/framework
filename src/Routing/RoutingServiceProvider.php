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

use Autarky\Container\ContainerInterface;
use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider for the FastRoute implementation.
 */
class RoutingServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->getContainer()->share('Autarky\Routing\RouterInterface', function(ContainerInterface $container) {
			return new Router(
				$container,
				$this->app->getConfig()->get('path.route-cache')
			);
		});

		$this->app->getContainer()->alias('Autarky\Routing\Router',
			'Autarky\Routing\RouterInterface');

		$this->app->config(function() {
			if ($routes = $this->app->getConfig()->get('routes')) {
				$router = $this->app->getContainer()
					->resolve('Autarky\Routing\RouterInterface');
				(new Configuration($router, $routes))->mount();
			}
		});
	}
}
