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

use Throwable;

/**
 * Class that can handle throwable.
 */
interface ErrorHandlerInterface
{
	/**
	 * Handle throwable.
	 *
	 * @param  \Throwable $throwable
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle(Throwable $throwable);
}
