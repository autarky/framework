<?php
namespace Autarky\Tests\Config;

use PHPUnit_Framework_TestCase;
use Autarky\Config\PhpFileStore;

class Test extends PHPUnit_Framework_TestCase
{
	public function makeConfig()
	{
		return new PhpFileStore(__DIR__.'/files');
	}

	/** @test */
	public function canGet()
	{
		$config = $this->makeConfig();
		$this->assertEquals('bar', $config->get('testfile.foo'));
	}

	/** @test */
	public function canSet()
	{
		$config = $this->makeConfig();
		$config->set('testfile.foo', 'baz');
		$this->assertEquals('baz', $config->get('testfile.foo'));
	}

	/** @test */
	public function canGetAndSet()
	{
		$config = $this->makeConfig();
		$this->assertEquals('bar', $config->get('testfile.foo'));
		$config->set('testfile.foo', 'baz');
		$this->assertEquals('baz', $config->get('testfile.foo'));
	}

	/** @test */
	public function getNonexistantKeys()
	{
		$config = $this->makeConfig();
		$this->assertEquals(null, $config->get('testfile.bar'));
		$this->assertEquals(null, $config->get('testfile.bar.baz'));
	}

	/** @test */
	public function getDefault()
	{
		$config = $this->makeConfig();
		$this->assertEquals('bar', $config->get('testfile.bar', 'bar'));
	}
}
