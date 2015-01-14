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
 * Factory argument that is class type-hinted.
 */
class ClassArgument extends AbstractArgument implements ArgumentInterface
{
	/**
	 * @var string
	 */
	protected $class;

	/**
	 * Constructor.
	 *
	 * @param int     $position
	 * @param string  $name
	 * @param string  $class
	 * @param boolean $required
	 */
	public function __construct($position, $name, $class, $required = true)
	{
		parent::__construct($position, $name, $required);
		$this->class = $class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isClass()
	{
		return true;
	}

	/**
	 * Get the name of the class type of the argument.
	 *
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}
}
