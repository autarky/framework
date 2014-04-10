<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Kernel;

use Exception;
use SplDoublyLinkedList;
use Symfony\Component\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class ErrorHandler
{
	public function __construct()
	{
		$this->handlers = new SplDoublyLinkedList;
	}

	public function getHandlers()
	{
		return $this->handlers;
	}

	public function appendHandler(callable $handler)
	{
		$this->handlers->push($handler);
	}

	public function prependHandler(callable $handler)
	{
		$this->handlers->unshift($handler);
	}

	public function register()
	{
		$this->errorHandler = SymfonyErrorHandler::register();
		$this->exceptionHandler = SymfonyExceptionHandler::register();
	}

	public function handle(Exception $exception)
	{
		foreach ($this->handlers as $handler) {
			$result = call_user_func($handler, $exception);

			if ($result !== null) {
				return $result;
			}
		}

		return $this->handleException($exception);
	}

	public function handleException(Exception $exception)
	{
		return $this->exceptionHandler->createResponse($exception);
	}
}
