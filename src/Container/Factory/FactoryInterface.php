<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Factory;

use Autarky\Container\ContainerInterface;

/**
 * Factory interface.
 */
interface FactoryInterface
{
	/**
	 * Get a new factory.
	 *
	 * @param  array  $params
	 *
	 * @return FactoryInterface
	 */
	public function getFactory(array $params = array());

	/**
	 * Invoke the factory.
	 *
	 * @param  ContainerInterface $container
	 * @param  array              $params
	 *
	 * @return mixed
	 */
	public function invoke(ContainerInterface $container, array $params = array());
}
