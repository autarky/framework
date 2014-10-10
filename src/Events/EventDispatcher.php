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

/**
 * Event dispatcher.
 *
 * Override Symfony's class to have access to resolving classes from the
 * container as event listeners.
 */
class EventDispatcher extends SymfonyEventDispatcher
{
	/**
	 * @var \Autarky\Events\ListenerResolver
	 */
	protected $resolver;

	/**
	 * @param ListenerResolver $resolver
	 */
	public function __construct(ListenerResolver $resolver)
	{
		$this->resolver = $resolver;
	}

	/**
	 * {@inheritdoc}
	 *
	 * The listener can be a string of 'Class:method' or just 'Class'. If no
	 * method is provided, the method 'handle' is used.
	 */
	public function addListener($name, $listener, $priority = 0)
	{
		if (is_string($listener) && !is_callable($listener)) {
			$listener = \Autarky\splitclm($listener, 'handle');
		}

		if (is_array($listener) && is_string($listener[0])) {
			list($class, $method) = $listener;
			$listener = function($event) use($class, $method) {
				return $this->resolver->resolve($class)
					->$method($event);
			};
		}

		parent::addListener($name, $listener, $priority);
	}
}
