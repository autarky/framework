<?php

use Mockery as m;
use Autarky\Tests\TestCase;

class DependantProviderTest extends TestCase
{
	/** @test */
	public function providerIsLoadedWhenDependenciesAreMet()
	{
		$app = $this->makeApplication(['DependencyProvider', 'SuccessfulDependantProvider']);
		$app->boot();
		$this->assertTrue(SuccessfulDependantProvider::$called);
	}

	/** @test */
	public function providerThrowsExceptionWithUnmetProviderDependency()
	{
		$app = $this->makeApplication(['SuccessfulDependantProvider']);
		$this->setExpectedException('Autarky\Providers\ProviderException', 'Errors while registering provider: SuccessfulDependantProvider - Provider must be loaded: DependencyProvider');
		$app->boot();
	}

	/** @test */
	public function providerThrowsExceptionWithUnmetClassDependency()
	{
		$app = $this->makeApplication(['UnsuccessfulClassDependantProvider']);
		$this->setExpectedException('Autarky\Providers\ProviderException', 'Errors while registering provider: UnsuccessfulClassDependantProvider - Class must exist: NonExistantClass');
		$app->boot();
	}

	/** @test */
	public function providerThrowsExceptionWithUnmetContainerDependency()
	{
		$app = $this->makeApplication(['UnsuccessfulContainerDependantProvider']);
		$this->setExpectedException('Autarky\Providers\ProviderException', 'Errors while registering provider: UnsuccessfulContainerDependantProvider - Class must be bound to the container: NonExistantInterface');
		$app->boot();
	}
}

class SuccessfulDependantProvider extends \Autarky\Providers\AbstractDependantProvider
{
	public static $called = false;
	public function register()
	{
		static::$called = true;
	}
	public function getClassDependencies()
	{
		return ['Autarky\Application'];
	}
	public function getContainerDependencies()
	{
		return ['Autarky\Container\ContainerInterface'];
	}
	public function getProviderDependencies()
	{
		return ['DependencyProvider'];
	}
}

class UnsuccessfulClassDependantProvider extends \Autarky\Providers\AbstractDependantProvider
{
	public function register() {}
	public function getClassDependencies()
	{
		return ['NonExistantClass'];
	}
}

class UnsuccessfulContainerDependantProvider extends \Autarky\Providers\AbstractDependantProvider
{
	public function register() {}
	public function getContainerDependencies()
	{
		return ['NonExistantInterface'];
	}
}

class DependencyProvider extends \Autarky\Providers\AbstractProvider
{
	public function register() {}
}
