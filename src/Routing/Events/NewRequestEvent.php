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
