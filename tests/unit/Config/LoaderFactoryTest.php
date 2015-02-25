<?php

use Mockery as m;

class LoaderFactoryTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	protected function makeFactory()
	{
		return new Autarky\Config\LoaderFactory(new Autarky\Container\Container);
	}

	/** @test */
	public function loaderIsRegisteredForExtension()
	{
		$factory = $this->makeFactory();
		$mock = m::mock('Autarky\Config\LoaderInterface');
		$factory->addLoader('.mock', $mock);
		$this->assertEquals($mock, $factory->getForPath('/foo/bar.mock'));
	}

	/** @test */
	public function invalidExtensionThrowsException()
	{
		$factory = $this->makeFactory();
		$this->setExpectedException('Autarky\Config\LoadException');
		$factory->getForPath('/foo/bar.baz');
	}
}
