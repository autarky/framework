<?php

use Autarky\Files\LockingFilesystem;

class LockingFilesystemTest extends PHPUnit_Framework_TestCase
{
	private function getTmpFilePath($suffix = null)
	{
		$path = TESTS_RSC_DIR.'/locked-files';
		if ($suffix === null) {
			return tempnam($path, __CLASS__);
		}
		return $path.'/'.__CLASS__.'-'.$suffix;
	}

	/** @test */
	public function canWriteToAndReadFromFile()
	{
		$path = $this->getTmpFilePath(__FUNCTION__);
		$op = new LockingFilesystem();
		$op->write($path, 'foo');
		$this->assertEquals('foo', file_get_contents($path));
		$this->assertEquals('foo', $op->read($path));
	}

	/**
	 * @test
	 * @dataProvider getLockTypes
	 */
	public function cannotWriteLockedFile($lock)
	{
		$path = $this->getTmpFilePath(__FUNCTION__);
		$file = fopen($path, 'c');
		flock($file, $lock);
		$op = new LockingFilesystem();
		$this->setExpectedException('Autarky\Files\IOException');
		$op->write($path, 'foo');
	}

	/**
	 * @test
	 * @dataProvider getLockTypes
	 */
	public function cannotReadExclusivelyLockedFile($lock)
	{
		$path = $this->getTmpFilePath(__FUNCTION__);
		$file = fopen($path, 'c');
		fwrite($file, 'foo');
		flock($file, $lock);
		$op = new LockingFilesystem();
		if ($lock & LOCK_EX) {
			$this->setExpectedException('Autarky\Files\IOException');
		}
		$this->assertEquals('foo', $op->read($path));
	}

	public function getLockTypes()
	{
		return [
			[LOCK_EX], [LOCK_SH], [LOCK_EX | LOCK_NB], [LOCK_SH | LOCK_NB],
		];
	}
}
