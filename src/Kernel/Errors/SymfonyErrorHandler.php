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
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class SymfonyErrorHandler extends AbstractErrorHandler
{
	/**
	 * {@inheritdoc}
	 */
	protected function defaultHandler(Exception $exception)
	{
		return (new SymfonyExceptionHandler($this->debug))
			->createResponse($exception);
	}
}
