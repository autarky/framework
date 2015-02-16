<?php

use Autarky\Files\LockingWriteOperation;

class LockingWriteOperationTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function canWriteToFile()
	{
		$path = tempnam(sys_get_temp_dir(), __CLASS__);
		$op = new LockingWriteOperation($path);
		$op->write('foo');
		$this->assertEquals('foo', file_get_contents($path));
	}

	/** @test */
	public function cannotWriteLockedFile()
	{
		$path = tempnam(sys_get_temp_dir(), __CLASS__);
		$file = fopen($path, 'c');
		flock($file, LOCK_EX);
		$op = new LockingWriteOperation($path);
		$this->setExpectedException('Autarky\Files\IOException');
		$op->write('foo');
	}
}
