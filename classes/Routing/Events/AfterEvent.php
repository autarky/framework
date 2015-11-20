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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Autarky\Routing\Route;

/**
 * Event that is fired after a route's controller has been invoked, before the
 * response is returned from the router to the HttpKernel.
 */
class AfterEvent extends AbstractRouteEvent
{
	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @param Request  $request
	 * @param Route    $route
	 * @param Response $response
	 */
	public function __construct(Request $request, Route $route, Response $response)
	{
		parent::__construct($request, $route);
		$this->response = $response;
	}

	/**
	 * Set the response object instance.
	 *
	 * @param Response $response
	 */
	public function setResponse(Response $response)
	{
		$this->response = $response;
	}

	/**
	 * Get the response object instance.
	 *
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
