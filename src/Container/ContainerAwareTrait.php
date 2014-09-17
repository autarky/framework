<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container;

/**
 * Simple implementation of ContainerAwareInterface that other classes can use.
 *
 * @see ContainerAwareInterface
 */
trait ContainerAwareTrait
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Set the container instance.
	 *
	 * @param ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}
}
