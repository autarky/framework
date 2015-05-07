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

use Autarky\Providers\AbstractProvider;

/**
 * Logging provider.
 *
 * Provides a channel manager which can store multiple loggers.
 */
class LoggingProvider extends AbstractProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$dic = $this->app->getContainer();

		$dic->define('Autarky\Logging\ChannelManager', function() {
			return new ChannelManager();
		});
		$dic->share('Autarky\Logging\ChannelManager');

		$factory = $dic->makeFactory(['Autarky\Logging\ChannelManager', 'getChannel']);
		$factory->addScalarArgument('$channel', 'string', false, null);
		$dic->define('Psr\Log\LoggerInterface', $factory);
	}
}
