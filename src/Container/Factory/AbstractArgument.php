<?php
namespace Autarky\Container\Factory;

abstract class AbstractArgument
{
	protected $position;
	protected $name;
	protected $required;

	public function __construct($position, $name, $required)
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

	public function getPosition()
	{
		return $this->position;
	}

	public function getName()
	{
		return $this->name;
	}

	public function isRequired()
	{
		return $this->required;
	}
}
