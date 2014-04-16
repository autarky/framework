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
use ReflectionClass;
use ReflectionFunction;
use SplDoublyLinkedList;
use Symfony\Component\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class ErrorHandler
{
	protected $handlers;
	protected $errorHandler;
	protected $exceptionHandler;
	protected $rethrow = false;

	public function __construct($rethrow = null)
	{
		$this->handlers = new SplDoublyLinkedList;

		if ($rethrow !== null) {
			$this->rethrow = (bool) $rethrow;
		} else if (php_sapi_name() === 'cli') {
			$this->rethrow = true;
		}
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

	public function register($debug = false)
	{
		if (!$this->rethrow) {
			$this->errorHandler = SymfonyErrorHandler::register(null, $debug);
			$this->exceptionHandler = SymfonyExceptionHandler::register($debug);
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
		return $this->exceptionHandler->createResponse($exception);
	}
}
