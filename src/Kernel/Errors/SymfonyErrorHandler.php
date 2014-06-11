<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Kernel\Errors;

use Exception;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

/**
 * The framework's error handler that takes care of caught and uncaught
 * exceptions as well as fatal PHP errors.
 */
class SymfonyErrorHandler extends AbstractErrorHandler
{
	/**
	 * Create a default error response.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function defaultHandler(Exception $exception)
	{
		return (new SymfonyExceptionHandler($this->debug))
			->createResponse($exception);
	}
}
