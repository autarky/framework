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

/**
 * Event that is fired before a route's controller is invoked.
 */
class BeforeEvent extends AbstractRouteEvent
{
	/**
	 * @var callable|null
	 */
	protected $controller;

	/**
	 * @var mixed
	 */
	protected $response;

	/**
	 * Set the dispatch's controller. This overrides the original route's
	 * controller.
	 *
	 * @param callable $controller
	 */
	public function setController($controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Get the dispatch's controller.
	 *
	 * @return callable|null
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Set the response.
	 *
	 * @param mixed $response
	 */
	public function setResponse($response)
	{
		$this->response = $response;
	}

	/**
	 * Get the response.
	 *
	 * @return mixed
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
