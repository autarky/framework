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

/**
 * Class that can handle exceptions.
 */
interface ErrorHandlerInterface
{
	/**
	 * Handle an exception.
	 *
	 * Note that this function can't be type-hinted for compatibility between
	 * PHP5 and PHP7.
	 *
	 * @param  \Throwable $exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle($exception);
}
