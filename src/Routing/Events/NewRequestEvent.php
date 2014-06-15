<?php
namespace Autarky\Routing\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class NewRequestEvent extends Event
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function getRequest()
	{
		return $this->request;
	}
}
