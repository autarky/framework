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
use Symfony\Component\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class ErrorHandler
{
	public function __construct()
	{
		// ...
	}

	public function register()
	{
		$this->errorHandler = SymfonyErrorHandler::register();
		$this->exceptionHandler = SymfonyExceptionHandler::register();
	}

	public function handle(Exception $exception)
	{
		return $this->exceptionHandler->createResponse($exception);
	}
}
