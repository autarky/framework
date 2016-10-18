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

/**
 * Stub error handler that simply re-throws the exceptions given.
 *
 * Written primarily for testing purposes.
 *
 * @codeCoverageIgnore
 */
class StubErrorHandler implements ErrorHandlerManagerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function setDebug($toggle)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRethrow($toggle)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLogger($logger)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendHandler($handler)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function prependHandler($handler)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultHandler(ErrorHandlerInterface $handler)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle($exception)
	{
		throw $exception;
	}
}
