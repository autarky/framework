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

use Autarky\Kernel\Application;

/**
 * The framework's error handler that takes care of caught and uncaught
 * exceptions as well as fatal PHP errors.
 */
interface ErrorHandlerInterface
{
	/**
	 * Set the error handler application.
	 *
	 * @param \Autarky\Kernel\Application $app
	 */
	public function setApplication(Application $app);

	/**
	 * Set whether the error handler is in debug mode or not.
	 *
	 * @param bool $toggle
	 */
	public function setDebug($toggle);

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
	 * @param  callable $handler
	 *
	 * @return void
	 */
	public function appendHandler(callable $handler);

	/**
	 * Prepend a handler to the list of handlers.
	 *
	 * @param  callable $handler
	 *
	 * @return void
	 */
	public function prependHandler(callable $handler);

	/**
	 * Register the error handler to handle uncaught exceptions and errors.
	 *
	 * @return void
	 */
	public function register();

	/**
	 * Handle an exception.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle(Exception $exception);
}
