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
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * The framework's error handler that takes care of caught and uncaught
 * exceptions as well as fatal PHP errors.
 */
class WhoopsErrorHandler extends AbstractErrorHandler
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
		if (!class_exists('Whoops\Run')) {
			return 'Composer package filp/whoops must be installed for WhoopsErrorHandler to work.';
		}

		$whoops = new Run();
		$whoops->allowQuit(false);
		$whoops->writeToOutput(false);
		$whoops->pushHandler(new PrettyPageHandler());

		return $whoops->handleException($exception);
	}
}
