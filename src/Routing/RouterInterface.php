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

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for routers that the framework can utilize.
 */
interface RouterInterface
{
	/**
	 * Dispatch a request in the router.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function dispatch(Request $request);

	/**
	 * Add a route to the router.
	 *
	 * @param string|array $methods HTTP methods the route should respond to
	 * @param string       $url     Relative URL the route should respond to. Parameters wrapped in {}
	 * @param mixed        $handler Closure or a string of "class:method"
	 * @param string       $name    Route name (optional)
	 */
	public function addRoute($method, $path, $handler, $name = null);

	/**
	 * Get the URL to a named route.
	 *
	 * @param  string $name
	 * @param  array  $params
	 *
	 * @return string
	 */
	public function getRouteUrl($name, array $params = array());
}
