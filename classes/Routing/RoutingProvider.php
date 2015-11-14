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
use Autarky\Providers\AbstractProvider;

/**
 * Simple service provider for the FastRoute implementation.
 */
class RoutingProvider extends AbstractProvider
{
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->define('Symfony\Component\HttpFoundation\RequestStack', function() {
			return $this->app->getRequestStack();
		});
		$dic->share('Symfony\Component\HttpFoundation\RequestStack');

		$dic->alias('FastRoute\RouteParser\Std', 'FastRoute\RouteParser');

		$dic->alias('Autarky\Routing\RoutePathGenerator', 'Autarky\Routing\RoutePathGeneratorInterface');

		$dic->define('Autarky\Routing\Router', function(ContainerInterface $container) {
			$eventDispatcher = 'Symfony\Component\EventDispatcher\EventDispatcherInterface';
			$eventDispatcher = $container->isBound($eventDispatcher)
				? $container->resolve($eventDispatcher) : null;

			$config = $this->app->getConfig();
			$cachePath = ($config && !$config->get('app.debug')) ?
				$config->get('path.route_cache') : null;

			return new Router(
				$container->resolve('FastRoute\RouteParser'),
				$container->resolve('Autarky\Routing\Invoker'),
				$eventDispatcher, $cachePath
			);
		});
		$dic->share('Autarky\Routing\Router');

		$dic->share('Autarky\Routing\UrlGenerator');

		$dic->share('Autarky\Routing\Invoker');

		$dic->alias('Autarky\Routing\Router',
			'Autarky\Routing\RouterInterface');
	}
}
