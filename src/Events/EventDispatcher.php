<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Events;

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

use Autarky\Container\ContainerInterface;

class EventDispatcher extends SymfonyEventDispatcher
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function addListener($name, $listener, $priority = 0)
	{
		if (is_string($listener)) {
			$listener = function($event) use($listener) {
				$segments = explode(':', $listener);
				$class = $segments[0];
				$method = isset($segments[1]) ? $segments[1] : 'handle';
				return $this->container->resolve($class)
					->$method($event);
			};
		}

		return parent::addListener($name, $listener, $priority);
	}
}
