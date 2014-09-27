<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Events;

use Symfony\Component\Console\Application;

use Autarky\Container\ContainerInterface;
use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider that binds a share instance of symfony's event
 * dispatcher onto the container.
 */
class EventDispatcherProvider extends ServiceProvider
{
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->define('Autarky\Events\EventDispatcher',
			function($dic) {
				return new EventDispatcher($dic);
			});
		$dic->share('Autarky\Events\EventDispatcher');

		$dic->alias('Autarky\Events\EventDispatcher',
			'Symfony\Component\EventDispatcher\EventDispatcherInterface');

		$dic->resolvingAny(function($obj, ContainerInterface $dic) {
			if ($obj instanceof EventDispatcherAwareInterface) {
				$obj->setEventDispatcher($dic->resolve('Autarky\Events\EventDispatcher'));
			}
		});
	}

	public function registerConsole(Application $console)
	{
		$eventDispatcher = $this->app->getContainer()
			->resolve('Symfony\Component\EventDispatcher\EventDispatcherInterface');
		$console->setDispatcher($eventDispatcher);
	}
}
