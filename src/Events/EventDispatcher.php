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

/**
 * Event dispatcher.
 *
 * Override Symfony's class to have access to resolving classes from the
 * container as event listeners.
 */
class EventDispatcher extends SymfonyEventDispatcher
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 *
	 * The listener can be a string of 'Class:method' or just 'Class'. If no
	 * method is provided, the method 'handle' is used.
	 */
	public function addListener($name, $listener, $priority = 0)
	{
		if (is_string($listener)) {
			list($class, $method) = \Autarky\splitclm($listener, 'handle');
			$listener = function($event) use($class, $method) {
				return $this->container->resolve($class)
					->$method($event);
			};
		}

		return parent::addListener($name, $listener, $priority);
	}
}
