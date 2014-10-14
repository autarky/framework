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

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

use Autarky\Routing\Route;

class AbstractRouteEvent extends Event
{
	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var Route
	 */
	protected $route;

	/**
	 * @param Request $request
	 * @param Route   $route
	 */
	public function __construct(Request $request, Route $route)
	{
		$this->request = $request;
		$this->route = $route;
	}

	/**
	 * Get the request instance.
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Get the route instance.
	 *
	 * @return Route
	 */
	public function getRoute()
	{
		return $this->route;
	}
}
