<?php

use Mockery as m;

use Autarky\Config\FileStore;
use Autarky\Config\LoaderFactory;

class FileStoreTest extends PHPUnit_Framework_TestCase
{
	protected function getConfigPath()
	{
		return TESTS_RSC_DIR.'/config';
	}

	protected function makeConfig($env = 'default')
	{
		$loaderFactory = new LoaderFactory(new \Autarky\Container\Container);
		$loaderFactory->addLoader('php', 'Autarky\Config\Loaders\PhpFileLoader');
		$loaderFactory->addLoader('yml', 'Autarky\Config\Loaders\YamlFileLoader');
		return new FileStore($loaderFactory, $this->getConfigPath(), $env);
	}

	/** @test */
	public function hasReturnsCorrectly()
	{
		$config = $this->makeConfig();
		$this->assertEquals(true, $config->has('testfile.foo'));
		$this->assertEquals(false, $config->has('testfile.bar'));
	}

	/** @test */
	public function getReturnsCorrectValue()
	{
		$config = $this->makeConfig();
		$this->assertEquals('bar', $config->get('testfile.foo'));
	}

	/** @test */
	public function setChangesValueInMemory()
	{
		$config = $this->makeConfig();
		$config->set('testfile.foo', 'baz');
		$this->assertEquals('baz', $config->get('testfile.foo'));
	}

	/** @test */
	public function setWorksAfterGetting()
	{
		$config = $this->makeConfig();
		$this->assertEquals('bar', $config->get('testfile.foo'));
		$config->set('testfile.foo', 'baz');
		$this->assertEquals('baz', $config->get('testfile.foo'));
	}

	/** @test */
	public function callingGetOnNonexistantKeysReturnsNull()
	{
		$config = $this->makeConfig();
		$this->assertEquals(null, $config->get('testfile.bar'));
		$this->assertEquals(null, $config->get('testfile.foo.bar'));
	}

	/** @test */
	public function getReturnsDefaultWhenKeyNotFound()
	{
		$config = $this->makeConfig();
		$this->assertEquals('bar', $config->get('testfile.bar', 'bar'));
	}

	/** @test */
	public function valuesAreOverridenDependingOnEnvironment()
	{
		$config = $this->makeConfig('dummyenv');
		$this->assertEquals('baz', $config->get('testfile.foo'));
	}

	/** @test */
	public function addNamespaceAndGetValueFromIt()
	{
		$config = $this->makeConfig();
		$config->addNamespace('namespace', $this->getConfigPath().'/vendor/namespace');
		$this->assertEquals('three', $config->get('namespace:testfile.three'));
	}

	/** @test */
	public function settingNamespacedDataLoadsTheRestOfTheNamespacedDataCorrectly()
	{
		$config = $this->makeConfig();
		$config->addNamespace('namespace', $this->getConfigPath().'/vendor/namespace');
		$config->set('namespace:testfile.three', 'THREE');
		$this->assertEquals('two', $config->get('namespace:testfile.two'));
		$this->assertEquals('THREE', $config->get('namespace:testfile.three'));
	}

	/** @test */
	public function overrideNamespaceWithCustomPath()
	{
		$config = $this->makeConfig();
		$config->addNamespace('namespace', $this->getConfigPath().'/vendor/namespace');
		$this->assertEquals('ONE', $config->get('namespace:testfile.one'));
	}

	/** @test */
	public function environmentOverridesWorkForNamespacedConfigs()
	{
		$config = $this->makeConfig('dummyenv');
		$config->addNamespace('namespace', $this->getConfigPath().'/vendor/namespace');
		$this->assertEquals('ONE', $config->get('namespace:testfile.one'));
	}

	/** @test */
	public function fileReturningNonArrayThrowsException()
	{
		$this->setExpectedException('RuntimeException');
		$config = $this->makeConfig();
		$config->get('notarray.foo');
	}

	/** @test */
	public function customConfigFileLoaderIsCalled()
	{
		$config = $this->makeConfig();
		$config->getLoaderFactory()->addLoader('mock', m::mock(['load' => ['foo' => 'bar']]));
		$this->assertEquals('bar', $config->get('mockedfile.foo'));
		$this->assertEquals(null, $config->get('mockedfile.bar'));
	}

	/** @test */
	public function yamlFilesCanBeParsed()
	{
		$config = $this->makeConfig();
		$config->getLoaderFactory()->addLoader('yml', 'Autarky\Config\Loaders\YamlFileLoader');
		$this->assertEquals('bar', $config->get('testyml.foo'));
	}
}
