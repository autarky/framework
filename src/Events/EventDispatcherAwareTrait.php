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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @see EventDispatcherAwareInterface
 */
trait EventDispatcherAwareTrait
{
	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * Set the event dispatcher instance.
	 *
	 * @param EventDispatcherInterface $eventDispatcher
	 */
	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}
}
