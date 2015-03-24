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
use Autarky\Providers\AbstractProvider;

/**
 * Simple service provider that binds a share instance of symfony's event
 * dispatcher onto the container.
 */
class EventDispatcherProvider extends AbstractProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->share('Autarky\Events\EventDispatcher');

		$dic->alias('Autarky\Events\EventDispatcher',
			'Symfony\Component\EventDispatcher\EventDispatcherInterface');

		$dic->resolvingAny(function($obj, ContainerInterface $dic) {
			if ($obj instanceof EventDispatcherAwareInterface) {
				$obj->setEventDispatcher($dic->resolve('Autarky\Events\EventDispatcher'));
			}
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerConsole(Application $console)
	{
		$eventDispatcher = $this->app->getContainer()
			->resolve('Symfony\Component\EventDispatcher\EventDispatcherInterface');
		$console->setDispatcher($eventDispatcher);
	}
}
