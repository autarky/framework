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

abstract class ContainerAware implements ContainerAwareInterface
{
	protected $container;

	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}
}
