<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Providers;

interface DependantProviderInterface extends ProviderInterface
{
	/**
	 * Get the classes the provider depends on.
	 *
	 * @return string[]
	 */
	public function getClassDependencies();

	/**
	 * Get the types the container must have bound.
	 *
	 * @return string[]
	 */
	public function getContainerDependencies();

	/**
	 * Get the class names of other providers the provider depends on.
	 *
	 * @return string[]
	 */
	public function getProviderDependencies();
}
