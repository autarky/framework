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
 * Interface for routers that the framework can utilize.
 */
interface RoutePathGeneratorInterface
{
	/**
	 * Set whether the regex pattern of route parameters should be validated on
	 * runtime.
	 *
	 * @param bool $validateParams
	 */
	public function setValidateParams($validateParams);

	/**
	 * Generate the path (relative URL) for a route.
	 *
	 * @param  Route  $route
	 * @param  array  $params
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getRoutePath(Route $route, array $params);
}
