<?php
namespace Autarky\Container\Factory;

class ScalarArgument extends AbstractArgument implements ArgumentInterface
{
	public function __construct($position, $name, $type, $required = true, $default = null)
	{
		parent::__construct($position, $name, $required);
		$this->type = $type;
		$this->default = $default;
	}

	public function isClass()
	{
		return false;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getDefault()
	{
		if ($this->required) {
			throw new \Exception('Argument is required and has no default');
		}

		return $this->default;
	}
}
