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
class StubErrorHandler implements ErrorHandlerManagerInterface
{
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

	public function appendHandler($handler)
	{
		//
	}

	public function prependHandler($handler)
	{
		//
	}

	public function setDefaultHandler(ErrorHandlerInterface $handler)
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

	public function handles(Exception $exception)
	{
		return true;
	}
}
