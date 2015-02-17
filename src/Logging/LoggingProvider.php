<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Logging;

use Autarky\Provider;
use Autarky\Container\ContainerInterface;

/**
 * Logging provider.
 *
 * Provides a channel manager which can store multiple loggers.
 */
class LoggingProvider extends Provider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->define('Autarky\Logging\ChannelManager',
			[$this, 'makeChannelManager']);
		$dic->share('Autarky\Logging\ChannelManager');
		$dic->define('Psr\Log\LoggerInterface',
			['Autarky\Logging\ChannelManager', 'getChannel']);
	}

	public function makeChannelManager(ContainerInterface $container)
	{
		return new ChannelManager();
	}
}
