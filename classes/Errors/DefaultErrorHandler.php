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
	public function handle(Exception $exception)
	{
		if ($this->debug && class_exists('Whoops\Run')) {
			return $this->handleWithWhoops($exception);
		}

		return $this->handleWithSymfony($exception);
	}

	protected function handleWithWhoops(Exception $exception)
	{
		$whoops = new Run();
		$whoops->allowQuit(false);
		$whoops->writeToOutput(false);
		$whoops->pushHandler(new PrettyPageHandler());

		return $whoops->handleException($exception);
	}

	protected function handleWithSymfony(Exception $exception)
	{
		if (!$exception instanceof FlattenException) {
			$exception = FlattenException::create($exception);
		}

		$handler = new ExceptionHandler($this->debug);

		return Response::create(
			$handler->getHtml($exception),
			$exception->getStatusCode(),
			$exception->getHeaders()
		);
	}
}
