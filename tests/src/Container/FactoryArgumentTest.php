<?php

use Autarky\Container\Factory\ClassArgument;
use Autarky\Container\Factory\ScalarArgument;

class FactoryArgumentTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function classArgument()
	{
		$arg = new ClassArgument(1, '$foo', 'Namespace\Class', true);
		$this->assertEquals(1, $arg->getPosition());
		$this->assertEquals('$foo', $arg->getName());
		$this->assertEquals(true, $arg->isClass());
		$this->assertEquals('Namespace\Class', $arg->getClass());
		$this->assertEquals(true, $arg->isRequired());
		$this->assertEquals(false, $arg->isOptional());
	}

	/** @test */
	public function scalarArgument()
	{
		$arg = new ScalarArgument(1, '$foo', ScalarArgument::TYPE_MIXED, false, 'bar');
		$this->assertEquals(1, $arg->getPosition());
		$this->assertEquals('$foo', $arg->getName());
		$this->assertEquals(ScalarArgument::TYPE_MIXED, $arg->getType());
		$this->assertEquals(false, $arg->isClass());
		$this->assertEquals(false, $arg->isRequired());
		$this->assertEquals(true, $arg->isOptional());
		$this->assertEquals('bar', $arg->getDefault());
	}
}
