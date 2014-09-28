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
class RoutingProvider extends ServiceProvider
{
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->define('Symfony\Component\HttpFoundation\RequestStack', function() {
			return $this->app->getRequestStack();
		});
		$dic->share('Symfony\Component\HttpFoundation\RequestStack');

		$dic->define('Autarky\Routing\Router', function(ContainerInterface $container) {
			return new Router(
				$container->resolve('Autarky\Routing\Invoker'),
				$this->app->getConfig()->get('path.route-cache')
			);
		});
		$dic->share('Autarky\Routing\Router');

		$dic->share('Autarky\Routing\UrlGenerator');

		$dic->share('Autarky\Routing\Invoker');

		$dic->alias('Autarky\Routing\Router',
			'Autarky\Routing\RouterInterface');
	}
}
