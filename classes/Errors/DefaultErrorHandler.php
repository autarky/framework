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
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * The default default error handler. Returns a whoops error screen if the
 * filp/whoops package is installed, otherwise a simple symfony error screen.
 *
 * @internal
 */
class DefaultErrorHandler implements ErrorHandlerInterface
{
	/**
	 * Whether or not debug is enabled.
	 *
	 * @var boolean
	 */
	protected $debug;

	/**
	 * @param boolean $debug
	 */
	public function __construct($debug)
	{
		$this->debug = (bool) $debug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle($exception)
	{
		if ($this->debug && class_exists('Whoops\Run')) {
			return $this->handleWithWhoops($exception);
		}

		return $this->handleWithSymfony($exception);
	}

	protected function handleWithWhoops($exception)
	{
		$whoops = new Run();
		$whoops->allowQuit(false);
		$whoops->writeToOutput(false);
		$whoops->pushHandler(new PrettyPageHandler());

		return $whoops->handleException($exception);
	}

	protected function handleWithSymfony($exception)
	{
		if (!$exception instanceof FlattenException) {
			$exception = static::flattenException($exception);
		}

		$handler = new ExceptionHandler($this->debug);

		return Response::create(
			$handler->getHtml($exception),
			$exception->getStatusCode(),
			$exception->getHeaders()
		);
	}

	protected static function flattenException($exception, $statusCode = null, array $headers = array())
	{
		if ($exception instanceof FlattenException) {
			return $exception;
		}

		$e = new FlattenException();
		$e->setMessage($exception->getMessage());
		$e->setCode($exception->getCode());

		if ($exception instanceof HttpExceptionInterface) {
			$statusCode = $exception->getStatusCode();
			$headers = array_merge($headers, $exception->getHeaders());
		}

		if (null === $statusCode) {
			$statusCode = 500;
		}

		$e->setStatusCode($statusCode);
		$e->setHeaders($headers);
		$e->setClass(get_class($exception));
		$e->setFile($exception->getFile());
		$e->setLine($exception->getLine());

		if ($exception instanceof \Exception) {
			$e->setTraceFromException($exception);
		}

		$previous = $exception->getPrevious();
		if ($previous) {
			$e->setPrevious(static::flattenException($previous));
		}

		return $e;
	}
}
