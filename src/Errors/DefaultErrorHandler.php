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
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class DefaultErrorHandler implements ErrorHandlerInterface
{
	protected $debug;

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

		return (new ExceptionHandler($this->debug))
			->createResponse($exception);
	}

	protected function handleWithWhoops(Exception $exception)
	{
		$whoops = new Run();
		$whoops->allowQuit(false);
		$whoops->writeToOutput(false);
		$whoops->pushHandler(new PrettyPageHandler());

		return $whoops->handleException($exception);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handles(Exception $exception)
	{
		return true;
	}
}
