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

use Closure;
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

	/**
	 * Get the current request the router is handling.
	 *
	 * @return \Symfoy\Component\HttpFoundation\Request
	 */
	public function getCurrentRequest();

	/**
	 * Get the root URL. Used to generate URLs to assets.
	 *
	 * @return string
	 */
	public function getRootUrl();

	/**
	 * Get the route matched to the current request.
	 *
	 * @return \Autarky\Routing\Route
	 */
	public function getCurrentRoute();

	/**
	 * Define a filter.
	 *
	 * @param  string           $name
	 * @param  \Closure|string  $handler
	 *
	 * @return void
	 */
	public function defineFilter($name, $handler);

	/**
	 * Define a route group.
	 *
	 * @param  array    $flags    Valid keys are 'before', 'after', 'prefix'
	 * @param  \Closure $callback First argument is the router ($this)
	 *
	 * @return void
	 */
	public function group(array $flags, Closure $callback);
}
