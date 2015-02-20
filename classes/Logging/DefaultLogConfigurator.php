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

use Autarky\Config\ConfigInterface;
use Autarky\Application;
use Autarky\ConfiguratorInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Default log configurator.
 *
 * You can easily change the path to log to by extending this class and
 * overriding either getLogDirectory, getLogFilename or getLogPath.
 */
class DefaultLogConfigurator implements ConfiguratorInterface
{
	const LOG_TS_FORMAT = 'Y-m-d H:i:s.u P';

	/**
	 * Channel manager instance.
	 *
	 * @var ChannelManager
	 */
	protected $channelManager;

	/**
	 * The config store instance.
	 *
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * The environment name.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Constructor.
	 *
	 * @param ChannelManager  $channelManager
	 * @param Application     $application
	 * @param ConfigInterface $config
	 */
	public function __construct(
		ChannelManager $channelManager,
		Application $application,
		ConfigInterface $config
	) {
		$this->environment = $application->getEnvironment();
		$this->channelManager = $channelManager;
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configure()
	{
		$this->channelManager->setChannel('default', $this->makeDefaultLogger());
	}

	protected function makeDefaultLogger()
	{
		$logger = new Logger($this->environment);

		if ($logpath = $this->getLogPath()) {
			$handler = new StreamHandler($logpath, Logger::DEBUG);
			$logger->pushHandler($handler);
			$formatter = new LineFormatter(null, static::LOG_TS_FORMAT, true);
			$handler->setFormatter($formatter);
		}

		return $logger;
	}

	protected function getLogPath()
	{
		if (!$logdir = $this->getLogDirectory()) {
			return null;
		}

		if (!is_dir($logdir)) {
			throw new \RuntimeException("Log directory $logdir does not exist or is not a directory.");
		}

		$logpath = rtrim($logdir, '\\/').DIRECTORY_SEPARATOR.$this->getLogFilename();

		if (file_exists($logpath) && !is_writable($logpath)) {
			throw new \RuntimeException("Log file $logpath is not writeable.");
		}

		return $logpath;
	}

	protected function getLogDirectory()
	{
		if ($this->config->has('path.logs')) {
			return $this->config->get('path.logs');
		}

		if ($this->config->has('path.storage')) {
			$path = $this->config->get('path.storage').'/logs';

			if (is_dir($path)) {
				return $path;
			}
		}

		return null;
	}

	protected function getLogFilename()
	{
		return PHP_SAPI.'.log';
	}
}
