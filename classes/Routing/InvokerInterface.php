<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing;

/**
 * Class that can invoke a route's callable.
 */
interface InvokerInterface
{
	/**
	 * Invoke a route's callable.
	 *
	 * @param  string|array $callable
	 * @param  array        $params
	 * @param  array        $constructorArgs
	 *
	 * @return mixed
	 */
	public function invoke($callable, array $params = [], $constructorArgs = []);
}
