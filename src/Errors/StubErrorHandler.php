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
use Autarky\Kernel\Application;

/**
 * Stub error handler that simply re-throws the exceptions given.
 *
 * Written primarily for testing purposes.
 */
class StubErrorHandler implements ErrorHandlerInterface
{
	public function setApplication(Application $app)
	{
		//
	}

	public function setDebug($toggle)
	{
		//
	}

	public function setRethrow($toggle)
	{
		//
	}

	public function setLogger($logger)
	{
		//
	}

	public function appendHandler(callable $handler)
	{
		//
	}

	public function prependHandler(callable $handler)
	{
		//
	}

	public function register()
	{
		//
	}

	public function handle(Exception $exception)
	{
		throw $exception;
	}
}
