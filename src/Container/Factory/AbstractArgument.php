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
 * Abstract factory argument class.
 */
abstract class AbstractArgument
{
	/**
	 * The position of the argument in the factory method.
	 *
	 * @var int
	 */
	protected $position;

	/**
	 * The variable name of the argument.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Whether or not the argument is required.
	 *
	 * @var boolean
	 */
	protected $required;

	/**
	 * Constructor.
	 *
	 * @param int     $position
	 * @param string  $name
	 * @param boolean $required
	 */
	public function __construct($position, $name, $required = true)
	{
		$this->position = (int) $position;

		if (!is_string($name) || strlen($name) < 1) {
			throw new \InvalidArgumentException('Argument name must be a non-empty string');
		}

		if ($name[0] !== '$') {
			$name = '$'.$name;
		}

		$this->name = $name;
		$this->required = (bool) $required;
	}

	/**
	 * {@inheridoc}
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * {@inheridoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheridoc}
	 */
	public function isRequired()
	{
		return $this->required;
	}

	/**
	 * {@inheridoc}
	 */
	public function isOptional()
	{
		return !$this->required;
	}
}
