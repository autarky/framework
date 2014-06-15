<?php
namespace Autarky\Routing\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

use Autarky\Routing\Route;

class RouteMatchedEvent extends Event
{
	protected $request;
	protected $route;

	public function __construct(Request $request, Route $route)
	{
		$this->request = $request;
		$this->route = $route;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getRoute()
	{
		return $this->route;
	}
}
