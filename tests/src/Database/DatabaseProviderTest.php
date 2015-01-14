<?php
namespace Autarky\Tests\Database;

use Autarky\Tests\TestCase;

class DatabaseProviderTest extends TestCase
{
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
		$app->resolve('PDO');
	}

	/** @test */
	public function canResolveCustomConnectionPdoAsDependency()
	{
		$app = $this->makeApplication('Autarky\Database\DatabaseProvider');
		$container = $app->getContainer();
		$app->getConfig()->set('database.connection', 'default');
		$app->getConfig()->set('database.connections', [
			'custom' => ['dsn' => 'sqlite::memory:'],
		]);
		$app->boot();

		$container->resolve(__NAMESPACE__.'\PdoDependentStub', [
			'PDO' => $container->getFactory('PDO', ['$connection' => 'custom']),
		]);

		$container->resolve(__NAMESPACE__.'\PdoDependentStub', [
			'$pdo' => $container->getFactory('PDO', ['$connection' => 'custom']),
		]);

		$container->params(__NAMESPACE__.'\PdoDependentStub', [
			'$pdo' => $container->getFactory('PDO', ['$connection' => 'custom']),
		]);
		$container->resolve(__NAMESPACE__.'\PdoDependentStub');
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
