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

abstract class AbstractDependantProvider extends AbstractProvider implements DependantProviderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getClassDependencies()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerDependencies()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProviderDependencies()
	{
		return [];
	}
}
