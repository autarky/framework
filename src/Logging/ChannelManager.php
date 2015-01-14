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

use InvalidArgumentException;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Manager of logging channels.
 */
class ChannelManager extends AbstractLogger implements LoggerInterface
{
	/**
	 * The name of the default channel to use.
	 *
	 * @var string
	 */
	protected $defaultChannel;

	/**
	 * The defined channels.
	 *
	 * @var LoggerInterface[]
	 */
	protected $channels = [];

	/**
	 * The deferred channel callbacks.
	 *
	 * @var callable[]
	 */
	protected $deferredChannels = [];

	/**
	 * Constructor.
	 *
	 * @param string $defaultChannel
	 */
	public function __construct($defaultChannel = 'default')
	{
		$this->defaultChannel = $defaultChannel;
	}

	/**
	 * Change the default channel.
	 *
	 * @param string $channel
	 */
	public function setDefaultChannel($channel)
	{
		$this->defaultChannel = $channel;
	}

	/**
	 * Get the name of the default channel.
	 *
	 * @return string
	 */
	public function getDefaultChannelName()
	{
		return $this->defaultChannel;
	}

	/**
	 * Set a channel instance.
	 *
	 * @param string          $channel
	 * @param LoggerInterface $logger
	 *
	 * @throws InvalidArgumentException If the channel is already defined.
	 */
	public function setChannel($channel, LoggerInterface $logger)
	{
		if (isset($this->channels[$channel])) {
			throw new InvalidArgumentException("Channel $channel is already defined");
		}

		$this->channels[$channel] = $logger;
	}

	/**
	 * Define a deferred channel. The callback will be invoked and the return
	 * value passed to setChannel. The return value must implement
	 * Psr\Log\LoggerInterface.
	 *
	 * @param string   $channel
	 * @param callable $callback Callback that takes no arguments
	 *
	 * @throws InvalidArgumentException If the channel is already defined.
	 */
	public function setDeferredChannel($channel, callable $callback)
	{
		if (isset($this->channels[$channel])) {
			throw new InvalidArgumentException("Channel $channel is already defined");
		}

		$this->deferredChannels[$channel] = $callback;
	}

	/**
	 * Get a specific channel.
	 *
	 * @param  string $channel  Optional - if none, use default channel
	 *
	 * @return \Psr\Log\LoggerInterface
	 *
	 * @throws InvalidArgumentException If the channel is not defined.
	 */
	public function getChannel($channel = null)
	{
		$channel = $channel ?: $this->defaultChannel;

		if (isset($this->deferredChannels[$channel])) {
			$this->setChannel($channel, $this->deferredChannels[$channel]());
			unset($this->deferredChannels[$channel]);
		}

		if (isset($this->channels[$channel])) {
			return $this->channels[$channel];
		}

		throw new InvalidArgumentException("Undefined channel: $channel");
	}

	/**
	 * {@inheritdoc}
	 */
	public function log($level, $message, array $context = array())
	{
		$this->getChannel($this->defaultChannel)
			->log($level, $message, $context);
	}
}