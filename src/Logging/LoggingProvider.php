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
		$dic = $this->app->getContainer();

		$dic->define('Monolog\Logger', [$this, 'makeLogger']);
		$dic->share('Monolog\Logger');
		$dic->alias('Monolog\Logger', 'Psr\Log\LoggerInterface');

		if ($errorHandler = $this->app->getErrorHandler()) {
			$errorHandler->setLogger(function() {
				return $this->app->resolve('Psr\Log\LoggerInterface');
			});
		}
	}

	public function makeLogger()
	{
		$logger = new Logger($this->app->getEnvironment());

		if ($logdir = $this->getLogDirectory()) {
			if (!is_dir($logdir)) {
				throw new \RuntimeException("Log directory $logdir does not exist or is not a directory.");
			}

			$logpath = rtrim($logdir, '\\/').'/'.PHP_SAPI.'.log';

			if (file_exists($logpath) && !is_writable($logpath)) {
				throw new \RuntimeException("Log file $logpath is not writeable.");
			}

			$logger->pushHandler($handler = new StreamHandler($logpath, Logger::DEBUG));
			$handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s.u P', true));
		}

		return $logger;
	}

	protected function getLogDirectory()
	{
		$config = $this->app->getConfig();

		if ($config->has('path.logs')) {
			return $config->get('path.logs');
		}

		if ($config->has('path.storage')) {
			$path = $config->get('path.storage').'/logs';

			// return null if the directory does not exist - no need to force
			// users to log
			if (is_dir($path)) {
				return $path;
			}
		}

		return null;
	}
}
