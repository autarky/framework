<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing\Events;

use Autarky\Routing\Route;

/**
 * Event that is fired when an URL is matched with a route.
 */
class RouteMatchedEvent extends AbstractRouteEvent
{
	/**
	 * Set the route, replacing the original one.
	 *
	 * @param Route $route
	 */
	public function setRoute(Route $route)
	{
		$this->route = $route;
	}
}
