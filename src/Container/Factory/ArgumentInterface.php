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

/**
 * Interface for factory argument classes.
 */
interface ArgumentInterface
{
	/**
	 * Get the argument's position.
	 *
	 * @return int
	 */
	public function getPosition();

	/**
	 * Get the argument's name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get whether the argument is required or not.
	 *
	 * @return boolean
	 */
	public function isRequired();

	/**
	 * Get whether the argument is optional or not.
	 *
	 * @return boolean
	 */
	public function isOptional();

	/**
	 * Whether the argument is a class.
	 *
	 * @return boolean
	 */
	public function isClass();
}
