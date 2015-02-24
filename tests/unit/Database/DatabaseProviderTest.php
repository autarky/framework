<?php

use Mockery as m;
use Autarky\Tests\TestCase;

class DatabaseProviderTest extends TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function canResolve()
	{
		$app = $this->makeApplication('Autarky\Database\DatabaseProvider');
		$app->boot();
		$object = $app->resolve('Autarky\Database\ConnectionManager');
		$this->assertInstanceOf('Autarky\Database\ConnectionManager', $object);
		$this->assertSame($object, $app->getContainer()->resolve('Autarky\Database\ConnectionManager'));
	}

	/** @test */
	public function canResolvePdo()
	{
		$app = $this->makeApplication('Autarky\Database\DatabaseProvider');
		$app->getConfig()->set('database.connection', 'default');
		$app->getConfig()->set('database.connections', [
			'default' => ['dsn' => 'sqlite::memory:'],
		]);
		$app->boot();
		$pdo = $app->resolve('PDO');
		$this->assertInstanceOf('PDO', $pdo);
	}

	/** @test */
	public function canResolveCustomConnectionPdoAsDependency()
	{
		$app = $this->makeApplication('Autarky\Database\DatabaseProvider');
		$container = $app->getContainer();
		$app->boot();
		$mock = m::mock('Autarky\Database\ConnectionManager');
		$mockPDO = new Autarky\Tests\DummyPDO;
		$mock->shouldReceive('getPdo')->with('custom')->times(3)
			->andReturn($mockPDO);
		$container->instance('Autarky\Database\ConnectionManager', $mock);

		$o = $container->resolve(__NAMESPACE__.'\PdoDependentStub', [
			'PDO' => $container->getFactory('PDO', ['$connection' => 'custom']),
		]);
		$this->assertSame($mockPDO, $o->pdo);

		$o = $container->resolve(__NAMESPACE__.'\PdoDependentStub', [
			'$pdo' => $container->getFactory('PDO', ['$connection' => 'custom']),
		]);
		$this->assertSame($mockPDO, $o->pdo);

		$container->params(__NAMESPACE__.'\PdoDependentStub', [
			'$pdo' => $container->getFactory('PDO', ['$connection' => 'custom']),
		]);
		$o = $container->resolve(__NAMESPACE__.'\PdoDependentStub');
		$this->assertSame($mockPDO, $o->pdo);
	}
}

class PdoDependentStub
{
	public $pdo;
	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
}
