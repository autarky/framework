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
 * Factory argument that is a scalar value.
 */
class ScalarArgument extends AbstractArgument implements ArgumentInterface
{
	/**
	 * The argument's type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The argument's default value, if it is not required.
	 *
	 * @var mixed
	 */
	protected $default;

	/**
	 * Constructor.
	 *
	 * @param integer $position
	 * @param string  $name
	 * @param string  $type
	 * @param boolean $required
	 * @param mixed   $default
	 */
	public function __construct($position, $name, $type, $required = true, $default = null)
	{
		parent::__construct($position, $name, $required);
		$this->type = $type;
		$this->default = $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isClass()
	{
		return false;
	}

	/**
	 * Get the argument's type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get the argument's default value, if it is not required.
	 *
	 * @return mixed
	 */
	public function getDefault()
	{
		if ($this->required) {
			throw new \Exception('Argument is required and has no default');
		}

		return $this->default;
	}
}
