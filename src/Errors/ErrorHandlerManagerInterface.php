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
 * The framework's error handler that takes care of caught and uncaught
 * exceptions as well as fatal PHP errors.
 */
interface ErrorHandlerManagerInterface extends ErrorHandlerInterface
{
	/**
	 * Set whether exceptions should be handled or rethrown.
	 *
	 * @param bool $toggle
	 */
	public function setRethrow($toggle);

	/**
	 * Set the error handler's logger.
	 *
	 * Can be a closure for the logger to be resolved lazily.
	 *
	 * @param \Psr\Log\LoggerInterface|\Closure $logger
	 */
	public function setLogger($logger);

	/**
	 * Append a handler to the list of handlers.
	 *
	 * @param  callable|ErrorHandlerInterface $handler
	 *
	 * @return void
	 */
	public function appendHandler($handler);

	/**
	 * Prepend a handler to the list of handlers.
	 *
	 * @param  callable|ErrorHandlerInterface $handler
	 *
	 * @return void
	 */
	public function prependHandler($handler);

	/**
	 * Set the default handler that will be called if no other handlers are
	 * available.
	 *
	 * @param ErrorHandlerInterface $handler
	 */
	public function setDefaultHandler(ErrorHandlerInterface $handler);

	/**
	 * Register the error handler to handle uncaught exceptions and errors.
	 *
	 * @return void
	 */
	public function register();
}
