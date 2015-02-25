<?php

class LocatorTest extends PHPUnit_Framework_TestCase
{
	public function makeLocator()
	{
		return new Autarky\Files\Locator();
	}

	/** @test */
	public function locatesFiles()
	{
		$basenames = [
			TESTS_RSC_DIR.'/files/root/filename',
			TESTS_RSC_DIR.'/files/other_root/filename',
		];
		$extensions = ['.foo', '.bar'];
		$paths = $this->makeLocator()->locate($basenames, $extensions);
		$expected = [
			TESTS_RSC_DIR.'/files/root/filename.foo',
			TESTS_RSC_DIR.'/files/other_root/filename.bar',
		];
		$this->assertEquals($expected, $paths);
	}
}
