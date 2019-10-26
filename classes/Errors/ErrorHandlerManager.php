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
use ReflectionMethod;
use SplDoublyLinkedList;
use Throwable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Manager that can handle throwables as well as keep track of multiple other
 * throwable handlers.
 */
class ErrorHandlerManager implements ErrorHandlerManagerInterface
{
	/**
	 * @var \Autarky\Errors\HandlerResolver
	 */
	protected $resolver;

	/**
	 * @var \SplDoublyLinkedList
	 */
	protected $handlers;

	/**
	 * @var ErrorHandlerInterface|null
	 */
	protected $defaultHandler;

	/**
	 * Re-throw throwables rather than handling them.
	 *
	 * @var boolean
	 */
	protected $rethrow = false;

	/**
	 * @param HandlerResolver           $resolver
	 */
	public function __construct(HandlerResolver $resolver)
	{
		$this->resolver = $resolver;
		$this->handlers = new SplDoublyLinkedList;
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
	 * {@inheritdoc}
	 */
	public function appendHandler($handler)
	{
		$this->checkHandler($handler);
		$this->handlers->push($handler);
	}

	/**
	 * {@inheritdoc}
	 */
	public function prependHandler($handler)
	{
		$this->checkHandler($handler);
		$this->handlers->unshift($handler);
	}

	protected function checkHandler($handler)
	{
		if (
			!$handler instanceof ErrorHandlerInterface
			&& !is_callable($handler)
			&& !is_string($handler)
		) {
			$type = is_object($handler) ? get_class($handler) : gettype($handler);
			throw new \InvalidArgumentException("Error handler must be callable, string or instance of Autarky\Errors\ErrorHandlerInterface, $type given");
		}
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
			set_exception_handler([$this, 'handleUncaught']);
			register_shutdown_function([$this, 'handleShutdown']);
		} else {
			register_shutdown_function([$this, 'throwFatalErrorException']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Throwable $throwable)
	{
		if ($this->rethrow) throw $throwable;

		foreach ($this->handlers as $index => $handler) {
			try {
				if (is_string($handler)) {
					$handler = $this->resolver->resolve($handler);
					$this->handlers->offsetSet($index, $handler);
				} else if (is_array($handler) && is_string($handler[0])) {
					$handler[0] = $this->resolver->resolve($handler[0]);
					$this->handlers->offsetSet($index, $handler);
				}

				if (!$this->matchesTypehint($handler, $throwable)) {
					continue;
				}

				$result = $this->callHandler($handler, $throwable);

				if ($result !== null) {
					return $this->makeResponse($result, $throwable);
				}
			} catch (Exception $newException) {
				return $this->handle($newException);
			}
		}

		return $this->makeResponse($this->defaultHandler->handle($throwable), $throwable);
	}

	/**
	 * Transform an exception handler's response into a Response object.
	 *
	 * @param  mixed      $response
	 * @param  \Throwable $throwable
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function makeResponse($response, Throwable $throwable)
	{
		if (!$response instanceof Response) {
			$response = new Response($response);
		}

		if (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
			if ($throwable instanceof HttpExceptionInterface) {
				$response->setStatusCode($throwable->getStatusCode());
				$response->headers->add($throwable->getHeaders());
			} else {
				$response->setStatusCode(500);
			}
		}

		return $response;
	}

	/**
	 * Check if a handler's argument typehint matches an exception.
	 *
	 * @param  callable|ErrorHandlerInterface $handler
	 * @param  \Throwable                     $throwable
	 *
	 * @return bool
	 */
	protected function matchesTypehint($handler, Throwable $throwable)
	{
		if ($handler instanceof ErrorHandlerInterface) {
			return true;
		}

		if (is_array($handler)) {
			$reflection = (new ReflectionMethod($handler[0], $handler[1]));
		} else {
			$reflection = (new ReflectionFunction($handler));
		}

		$params = $reflection->getParameters();

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

		return $handlerHint->isInstance($throwable);
	}

	/**
	 * Call an exception handler.
	 *
	 * @param  mixed     $handler
	 * @param  Throwable $throwable
	 *
	 * @return mixed
	 */
	protected function callHandler($handler, Throwable $throwable)
	{
		if ($handler instanceof ErrorHandlerInterface) {
			return $handler->handle($throwable);
		}

		return call_user_func($handler, $throwable);
	}

	/**
	 * Handle an uncaught exception. Does the same as handle(), but also sends
	 * the response, as we can assume that the exception happened outside of
	 * HttpKernelInterface::handle.
	 *
	 * @param  \Throwable $throwable
	 *
	 * @return Response
	 *
	 * @throws Throwable  If PHP_SAPI is 'cli'
	 */
	public function handleUncaught($throwable)
	{
		if (PHP_SAPI === 'cli') {
			throw $throwable;
		}

		return $this->handle($throwable)
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
	protected function makeFatalErrorException()
	{
		$error = error_get_last();

		if ($error !== null) {
			return new FatalErrorException($error['message'],
				$error['type'], 0, $error['file'], $error['line']);
		}

		return null;
	}
}
