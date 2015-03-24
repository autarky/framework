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
use Exception;
use Psr\Log\LoggerInterface;

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

	public function handle(Exception $exception)
	{
		$this->logger->error($exception);
	}
}
