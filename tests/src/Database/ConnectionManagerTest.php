<?php
namespace Autarky\Tests\Database;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use PDO;

class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function makeManager($config)
	{
		return new \Autarky\Database\ConnectionManager($config);
	}

	public function makeConfig($connection = 'default', array $connections = array())
	{
		return new \Autarky\Config\ArrayStore(['database' => ['connection' => $connection, 'connections' => $connections]]);
	}

	/** @test */
	public function getDefaultConnection()
	{
		$connections = ['default' => ['dsn' => 'sqlite::memory:']];
		$config = $this->makeConfig('default', $connections);
		$container = $this->makeManager($config);
		$pdo = $container->getPdo();
		$this->assertInstanceOf('PDO', $pdo);
		$this->assertSame($pdo, $container->getPdo());
	}

	/** @test */
	public function getNonDefaultConnection()
	{
		$connections = [
			'default' => ['dsn' => 'sqlite::memory:'],
			'other' => ['dsn' => 'sqlite::memory:']
		];
		$config = $this->makeConfig('default', $connections);
		$container = $this->makeManager($config);
		$pdo = $container->getPdo('other');
		$this->assertInstanceOf('PDO', $pdo);
		$this->assertNotSame($pdo, $container->getPdo());
	}

	/** @test */
	public function undefinedConnectionThrowsException()
	{
		$connections = ['default' => ['dsn' => 'sqlite::memory:']];
		$config = $this->makeConfig('default', $connections);
		$container = $this->makeManager($config);
		$this->setExpectedException('InvalidArgumentException');
		$pdo = $container->getPdo('other');
	}

	/** @test */
	public function missingDsnThrowsException()
	{
		$connections = ['default' => []];
		$config = $this->makeConfig('default', $connections);
		$container = $this->makeManager($config);
		$this->setExpectedException('InvalidArgumentException');
		$pdo = $container->getPdo('other');
	}
}
