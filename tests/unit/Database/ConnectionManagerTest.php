<?php

use Mockery as m;
use Autarky\Tests\DummyPDO;

class ConnectionManagerTest extends PHPUnit\Framework\TestCase
{
	protected $manager;
	protected $config;
	protected $factory;

	public function tearDown()
	{
		m::close();
	}

	private function makeManager($config = null, $factory = null)
	{
		return $this->manager = new \Autarky\Database\ConnectionManager(
			$this->config = ($config ?: $this->makeConfig()),
			$this->factory = ($factory ?: $this->mockFactory())
		);
	}

	private function makeConfig($connection = 'default', array $connections = [])
	{
		return new \Autarky\Config\ArrayStore(['database' => ['connection' => $connection, 'connections' => $connections]]);
	}

	private function mockFactory()
	{
		return m::mock('Autarky\Database\ConnectionFactory');
	}

	/** @test */
	public function getDefaultConnection()
	{
		$manager = $this->makeManager();
		$config = ['default' => ['dsn' => 'sqlite::memory:']];
		$this->config->set('database.connections', $config);
		$this->factory->shouldReceive('makePdo')->once()
			->with($config['default'], 'default')
			->andReturn(new DummyPDO);

		$pdo = $manager->getPdo();

		$this->assertInstanceOf('PDO', $pdo);
		$this->assertSame($pdo, $manager->getPdo());
	}

	/** @test */
	public function getNonDefaultConnection()
	{
		$manager = $this->makeManager();
		$config = [
			'default' => ['dsn' => 'sqlite::memory:'],
			'other' => ['dsn' => 'sqlite::memory:']
		];
		$this->config->set('database.connections', $config);
		$this->factory->shouldReceive('makePdo')->once()
			->with($config['default'], 'default')->andReturn(new DummyPDO);
		$this->factory->shouldReceive('makePdo')->once()
			->with($config['other'], 'other')->andReturn(new DummyPDO);

		$pdo = $manager->getPdo('other');

		$this->assertInstanceOf('PDO', $pdo);
		$this->assertNotSame($pdo, $manager->getPdo('default'));
	}

	/** @test */
	public function undefinedConnectionThrowsException()
	{
		$manager = $this->makeManager();
		$config = ['default' => ['dsn' => 'sqlite::memory:']];
		$this->config->set('database.connections', $config);

		$this->expectException('InvalidArgumentException',
			'No config found for connection: other');
		$pdo = $manager->getPdo('other');
	}
}
