<?php
namespace Autarky\Container\Factory;

class ClassArgument extends AbstractArgument implements ArgumentInterface
{
	public function __construct($position, $name, $class, $required = true)
	{
		parent::__construct($position, $name, $required);
		$this->class = $class;
	}

	public function isClass()
	{
		return true;
	}

	public function getClass()
	{
		return $this->class;
	}
}
