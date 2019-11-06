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

use Autarky\Errors\ErrorHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * This simple error handler logs exceptions.
 */
class LoggingErrorHandler implements ErrorHandlerInterface
{
	protected $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function handle(Throwable $throwable)
	{
		$this->logger->error($throwable);
	}
}
