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
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

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

	public function getHandlers()
	{
		return $this->handlers;
	}

	public function appendHandler(callable $handler)
	{
		$this->handlers->push($handler);
	}

	public function prependHandler(callable $handler)
	{
		$this->handlers->unshift($handler);
	}

	public function register()
	{
		if (!$this->rethrow) {
			ini_set('display_errors', 0);
			set_exception_handler([$this, 'handleUncaught']);
			set_error_handler([$this, 'handleError']);
			register_shutdown_function([$this, 'handleShutdown']);
		}
	}

	public function handle(Exception $exception)
	{
		if ($this->rethrow) throw $exception;

		foreach ($this->handlers as $handler) {
			if (!$this->matchesTypehint($handler, $exception)) continue;

			$result = call_user_func($handler, $exception);

			if ($result !== null) {
				return $result;
			}
		}

		return $this->defaultHandler($exception);
	}

	protected function matchesTypehint(callable $handler, Exception $exception)
	{
		$params = (new ReflectionFunction($handler))
			->getParameters();

		if (empty($params)) {
			return true;
		}

		$handlerHint = $params[0]
			->getClass();

		if (!$handlerHint) {
			return true;
		}

		return $handlerHint->isInstance($exception);
	}

	protected function defaultHandler(Exception $exception)
	{
		return (new SymfonyExceptionHandler($this->debug))
			->createResponse($exception);
	}

	public function handleUncaught(Exception $exception)
	{
		return $this->handle($exception)
			->send();
	}

	public function handleError($level, $message, $file = '', $line = 0, $context = array())
	{
		if (error_reporting() & $level) {
			throw new ErrorException($message, 0, $level, $file, $line);
		}
	}

	public function handleShutdown()
	{
		$error = error_get_last();

		if ($error !== null) {
			extract($error);
			$this->handleUncaught(new FatalErrorException($message, $type, 0, $file, $line));
		}
	}
}
