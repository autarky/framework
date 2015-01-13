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

use Autarky\Kernel\ServiceProvider;
use Autarky\Container\ContainerInterface;

/**
 * Logging provider.
 *
 * Provides a channel manager which can store multiple loggers.
 */
class LoggingProvider extends ServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->dic = $this->app->getContainer();

		$this->dic->define('Autarky\Logging\ChannelManager',
			[$this, 'makeChannelManager']);
		$this->dic->share('Autarky\Logging\ChannelManager');
		$this->dic->define('Psr\Log\LoggerInterface',
			['Autarky\Logging\ChannelManager', 'getChannel']);
	}

	public function makeChannelManager(ContainerInterface $container)
	{
		return new ChannelManager();
	}
}
