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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

use Autarky\Kernel\ServiceProvider;
use Autarky\Container\ContainerInterface;

/**
 * Simple service provider that binds a Monolog instance onto the container and
 * registers an error handler that logs all errors.
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
		return new ChannelManager;
	}
}
