<?php

use Mockery as m;

use Autarky\Config\FileStore;
use Autarky\Config\LoaderFactory;

class FileStoreTest extends PHPUnit_Framework_TestCase
{
	protected function getConfigPath()
	{
		return TESTS_RSC_DIR.'/config/app';
	}

	protected function makeConfig($env = 'default')
	{
		$pathResolver = new Autarky\Files\PathResolver($this->getConfigPath());
		$fileLocator = new Autarky\Files\Locator();
		$loaderFactory = new LoaderFactory(new \Autarky\Container\Container);
		$loaderFactory->addLoader('.php', 'Autarky\Config\Loaders\PhpFileLoader');
		$loaderFactory->addLoader('.yml', 'Autarky\Config\Loaders\YamlFileLoader');
		return new FileStore($pathResolver, $fileLocator, $loaderFactory, $env);
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
	public function mountAndGetValueFromIt()
	{
		$config = $this->makeConfig();
		$config->mount('namespace', $this->getConfigPath().'/../vendor/namespace');
		$this->assertEquals('three', $config->get('namespace/testfile.three'));
	}

	/** @test */
	public function settingNamespacedDataLoadsTheRestOfTheNamespacedDataCorrectly()
	{
		$config = $this->makeConfig();
		$config->mount('namespace', $this->getConfigPath().'/../vendor/namespace');
		$config->set('namespace/testfile.three', 'THREE');
		$this->assertEquals('two', $config->get('namespace/testfile.two'));
		$this->assertEquals('THREE', $config->get('namespace/testfile.three'));
	}

	/** @test */
	public function overrideNamespaceWithCustomPath()
	{
		$config = $this->makeConfig();
		$config->mount('namespace', $this->getConfigPath().'/../vendor/namespace');
		$this->assertEquals('ONE', $config->get('namespace/testfile.one'));
		$this->assertEquals('two', $config->get('namespace/testfile.two'));
	}

	/** @test */
	public function environmentOverridesWorkForNamespacedConfigs()
	{
		$config = $this->makeConfig('dummyenv');
		$config->mount('namespace', $this->getConfigPath().'/../vendor/namespace');
		$this->assertEquals('ONE', $config->get('namespace/testfile.one'));
		$this->assertEquals('TWO', $config->get('namespace/testfile.two'));
	}

	/** @test */
	public function fileReturningNonArrayThrowsException()
	{
		$config = $this->makeConfig();
		$this->setExpectedException('Autarky\Config\LoadException');
		$config->get('notarray.foo');
	}

	/** @test */
	public function customConfigFileLoaderIsCalled()
	{
		$config = $this->makeConfig();
		$mock = m::mock('Autarky\Config\LoaderInterface');
		$mock->shouldReceive('load')->once()
			->with(TESTS_RSC_DIR.'/config/app/mockedfile.mock')
			->andReturn(['foo' => 'bar']);
		$config->getLoaderFactory()->addLoader('.mock', $mock);
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
