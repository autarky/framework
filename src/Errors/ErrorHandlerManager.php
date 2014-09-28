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
use ErrorException;
use ReflectionFunction;
use SplDoublyLinkedList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Autarky\Kernel\Application;

class ErrorHandlerManager implements ErrorHandlerManagerInterface
{
	/**
	 * @var \Autarky\Kernel\Application
	 */
	protected $app;

	/**
	 * @var \Closure|\Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \SplDoublyLinkedList
	 */
	protected $handlers;

	/**
	 * @var ErrorHandlerInterface|null
	 */
	protected $defaultHandler;

	/**
	 * Debug mode.
	 *
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Re-throw exceptions rather than handling them.
	 *
	 * @var boolean
	 */
	protected $rethrow = false;

	public function __construct()
	{
		$this->handlers = new SplDoublyLinkedList;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setApplication(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDebug($debug)
	{
		$this->debug = (bool) $debug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRethrow($rethrow)
	{
		if ($rethrow !== null) {
			$this->rethrow = (bool) $rethrow;
		} else if (PHP_SAPI === 'cli') {
			$this->rethrow = true;
		} else {
			$this->rethrow = false;
		}
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
	 * {@inheritdoc}
	 */
	public function setLogger($logger)
	{
		$this->logger = $logger;
	}

	public function getLogger()
	{
		if ($this->logger instanceof \Closure) {
			$this->logger = call_user_func($this->logger);
		}

		return $this->logger;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendHandler(callable $handler)
	{
		$this->handlers->push($handler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function prependHandler(callable $handler)
	{
		$this->handlers->unshift($handler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultHandler(ErrorHandlerInterface $handler)
	{
		$this->defaultHandler = $handler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		set_error_handler([$this, 'handleError']);

		if (!$this->rethrow) {
			ini_set('display_errors', 0);
			set_exception_handler([$this, 'handleUncaught']);
			register_shutdown_function([$this, 'handleShutdown']);
		} else {
			register_shutdown_function([$this, 'throwFatalErrorException']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Exception $exception)
	{
		if ($this->rethrow) throw $exception;

		$this->logException($exception);

		foreach ($this->handlers as $handler) {
			if (!$this->matchesTypehint($handler, $exception)) continue;

			$result = call_user_func($handler, $exception);

			if ($result !== null) {
				return $this->makeResponse($result, $exception);
			}
		}

		return $this->makeResponse($this->defaultHandler($exception), $exception);
	}

	/**
	 * Log an exception if a logger has been set.
	 *
	 * @param  \Exception $exception
	 *
	 * @return void
	 */
	protected function logException(Exception $exception)
	{
		if ($this->logger === null) return;

		$this->getLogger()->error($exception, $this->getContext());
	}

	/**
	 * Get an array of context data for the application.
	 *
	 * @return array
	 */
	protected function getContext()
	{
		$request = $this->app->getRequestStack()->getCurrentRequest();
		$route = $this->app->getRouter()->getCurrentRoute();
		$routeName = ($route && $route->getName()) ? $route->getName() : 'No route';

		return [
			'method' => $request ? $request->getMethod() : null,
			'uri' => $request ? $request->getRequestUri() : null,
			'name' => $routeName,
		];
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
			$headers = $exception->getHeaders();
		} else {
			$statusCode = 500;
			$headers = [];
		}

		return new Response($result, $statusCode, $headers);
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
		if (PHP_SAPI === 'cli') {
			throw $exception;
		}

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
		$exception = $this->makeFatalErrorException();

		if ($exception) {
			$this->handleUncaught($exception);
		}
	}

	/**
	 * Throw a FatalErrorException if an error has occured.
	 *
	 * @return void
	 *
	 * @throws \Symfony\Component\Debug\Exception\FatalErrorException
	 */
	public function throwFatalErrorException()
	{
		$exception = $this->makeFatalErrorException();

		if ($exception) throw $exception;
	}

	/**
	 * Create a FatalErrorException out of the information stored on the last
	 * PHP error.
	 *
	 * @return \Symfony\Component\Debug\Exception\FatalErrorException|null
	 */
	public function makeFatalErrorException()
	{
		$error = error_get_last();

		if ($error !== null) {
			return new FatalErrorException($error['message'],
				$error['type'], 0, $error['file'], $error['line']);
		}
	}
}
