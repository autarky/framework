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
use ErrorException;
use ReflectionClass;
use ReflectionFunction;
use SplDoublyLinkedList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

/**
 * The framework's error handler that takes care of caught and uncaught
 * exceptions as well as fatal PHP errors.
 */
class ErrorHandler
{
	protected $handlers;
	protected $debug = false;
	protected $rethrow = false;

	public function __construct($debug = false, $rethrow = null)
	{
		$this->handlers = new SplDoublyLinkedList;

		if ($rethrow !== null) {
			$this->rethrow = (bool) $rethrow;
		} else if (php_sapi_name() === 'cli') {
			$this->rethrow = true;
		}

		$this->debug = (bool) $debug;
	}

	/**
	 * Get the handlers.
	 *
	 * @return \SplDoublyLinkedList
	 */
	public function getHandlers()
	{
		return $this->handlers;
	}

	/**
	 * Append a handler to the list of handlers.
	 *
	 * @param  callable $handler
	 *
	 * @return void
	 */
	public function appendHandler(callable $handler)
	{
		$this->handlers->push($handler);
	}

	/**
	 * Prepend a handler to the list of handlers.
	 *
	 * @param  callable $handler
	 *
	 * @return void
	 */
	public function prependHandler(callable $handler)
	{
		$this->handlers->unshift($handler);
	}

	/**
	 * Register the error handler to handle uncaught exceptions and errors.
	 *
	 * @return void
	 */
	public function register()
	{
		if (!$this->rethrow) {
			ini_set('display_errors', 0);
			set_exception_handler([$this, 'handleUncaught']);
			set_error_handler([$this, 'handleError']);
			register_shutdown_function([$this, 'handleShutdown']);
		}
	}

	/**
	 * Handle an exception.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle(Exception $exception)
	{
		if ($this->rethrow) throw $exception;

		foreach ($this->handlers as $handler) {
			if (!$this->matchesTypehint($handler, $exception)) continue;

			$result = call_user_func($handler, $exception);

			if ($result !== null) {
				return $this->makeResponse($result, $exception);
			}
		}

		return $this->defaultHandler($exception);
	}

	/**
	 * Transform an exception handler's response into a Response object.
	 *
	 * @param  mixed      $result
	 * @param  \Exception $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function makeResponse($result, Exception $exception)
	{
		if ($result instanceof Response) {
			return $result;
		}

		if ($exception instanceof HttpExceptionInterface) {
			$statusCode = $exception->getStatusCode();
		} else {
			$statusCode = 500;
		}

		return new Response($result, $statusCode);
	}

	/**
	 * Check if a handler's argument typehint matches an exception.
	 *
	 * @param  callable   $handler
	 * @param  \Exception $exception
	 *
	 * @return bool
	 */
	protected function matchesTypehint(callable $handler, Exception $exception)
	{
		$params = (new ReflectionFunction($handler))
			->getParameters();

		// if the handler takes no parameters it is considered global and should
		// handle every exception
		if (empty($params)) {
			return true;
		}

		$handlerHint = $params[0]
			->getClass();

		// likewise, if the first handler parameter has no typehint, consider it
		// a global handler that handles everything
		if (!$handlerHint) {
			return true;
		}

		return $handlerHint->isInstance($exception);
	}

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

	/**
	 * Handle an uncaught exception. Does the same as handle(), but also sends
	 * the response, as we can assume that the exception happened outside of
	 * HttpKernelInterface::handle.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handleUncaught(Exception $exception)
	{
		return $this->handle($exception)
			->send();
	}

	/**
	 * Handle a PHP error.
	 *
	 * @param  int     $level
	 * @param  string  $message
	 * @param  string  $file
	 * @param  int     $line
	 * @param  array   $context
	 *
	 * @throws \ErrorException if the error level matches PHP's error reporting.
	 */
	public function handleError($level, $message, $file = '', $line = 0, $context = array())
	{
		if (error_reporting() & $level) {
			throw new ErrorException($message, 0, $level, $file, $line);
		}
	}

	/**
	 * Handle a PHP fatal error.
	 *
	 * @return void
	 */
	public function handleShutdown()
	{
		$error = error_get_last();

		if ($error !== null) {
			extract($error);
			$exception = new FatalErrorException($message, $type, 0, $file, $line);
			return $this->handleUncaught($exception);
		}
	}
}
