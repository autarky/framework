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

use Symfony\Component\EventDispatcher\EventDispatcher;

use Autarky\Kernel\ServiceProvider;

/**
 * Simple service provider that binds a share instance of symfony's event
 * dispatcher onto the container.
 */
class EventServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->getContainer()->share(
			'Symfony\Component\EventDispatcher\EventDispatcherInterface',
			function() {
				return new EventDispatcher;
			});

		$this->app->getContainer()->alias(
			'Symfony\Component\EventDispatcher\EventDispatcher',
			'Symfony\Component\EventDispatcher\EventDispatcherInterface');
	}
}
