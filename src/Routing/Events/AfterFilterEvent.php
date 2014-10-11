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

class AfterFilterEvent extends AbstractRouteEvent
{
	protected $response;

	public function __construct(Request $request, Route $route, Response $response)
	{
		parent::__construct($request, $route);
		$this->response = $response;
	}

	public function setResponse(Response $response)
	{
		$this->response = $response;
	}

	public function getResponse()
	{
		return $this->response;
	}
}
