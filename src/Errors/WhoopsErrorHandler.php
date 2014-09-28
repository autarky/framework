<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Errors;

use Exception;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class WhoopsErrorHandler implements ErrorHandlerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function handle(Exception $exception)
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

	/**
	 * {@inheritdoc}
	 */
	public function handles(Exception $exception)
	{
		return true;
	}
}
