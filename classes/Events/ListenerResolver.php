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

use Autarky\Container\ContainerInterface;

/**
 * Class that resolves listeners.
 *
 * @internal
 */
class ListenerResolver
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolve a listener.
	 *
	 * @param  string $handler
	 *
	 * @return object
	 */
	public function resolve($handler)
	{
		return $this->container->resolve($handler);
	}
}
