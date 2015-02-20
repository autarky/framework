<?php

use Mockery as m;

class ConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	private function makeFactory($instantiator = null)
	{
		return new Autarky\Database\ConnectionFactory($instantiator);
	}

	private function mockInstantiator()
	{
		$mock = m::mock('Autarky\Database\PDOInstantiator');
		$mock->shouldReceive('instantiate')->andReturnUsing(function() {
			return call_user_func_array('Autarky\Tests\SpyPDO::create', func_get_args());
		})->byDefault();
		return $mock;
	}

	/** @test */
	public function missingDsnOrDriverThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException', 'DSN or driver must be set');
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo([]);
	}

	/** @test */
	public function missingUsernameThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException',
			'Missing username for connection: test');
		$cfg = ['dsn' => 'pgsql:host=localhost', 'username' => ''];
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($cfg, 'test');
	}

	/** @test */
	public function missingPasswordThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException',
			'Missing password for connection: test');
		$cfg = ['dsn' => 'pgsql:host=localhost', 'username' => 'foo'];
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($cfg, 'test');
	}

	/** @test */
	public function missingHostThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException',
			'Missing host for connection: test');
		$cfg = ['driver' => 'pgsql', 'username' => 'foo', 'password' => 'foo'];
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($cfg, 'test');
	}

	/** @test */
	public function missingDbnameThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException',
			'Missing dbname for connection: test');
		$cfg = ['driver' => 'pgsql', 'host' => 'localhost',
		        'username' => 'foo', 'password' => 'foo'];
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($cfg, 'test');
	}

	public function getSqliteConfigs()
	{
		return [[
			['dsn' => 'sqlite::memory:']
		],[
			['driver' => 'sqlite', 'path' => ':memory:']
		]];
	}

	/**
	 * @test
	 * @dataProvider getSqliteConfigs
	 */
	public function getSqliteConnectionWithDsn($config)
	{
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($config);
		$this->assertInstanceOf('Autarky\Tests\SpyPDO', $pdo);
		$this->assertEquals('sqlite::memory:', $pdo->getDsn());
	}

	/** @test */
	public function missingSqlitePathThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException',
			'Missing path for connection: test');
		$cfg = ['driver' => 'sqlite'];
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($cfg, 'test');
	}

	public function getRegularConfigs()
	{
		return [[[
			'dsn' => 'pgsql:host=localhost;port=1234;dbname=fakedb',
			'username' => 'fakeuser',
			'password' => 'fakepass',
		]],[[
			'driver' => 'pgsql',
			'host' => 'localhost',
			'port' => '1234',
			'dbname' => 'fakedb',
			'username' => 'fakeuser',
			'password' => 'fakepass',
		]]];
	}

	/**
	 * @test
	 * @dataProvider getRegularConfigs
	 */
	public function getRegularConnection($config)
	{
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($config);
		$this->assertInstanceOf('Autarky\Tests\SpyPDO', $pdo);
		$this->assertEquals('pgsql:host=localhost;port=1234;dbname=fakedb', $pdo->getDsn());
		$this->assertEquals('fakeuser', $pdo->getUsername());
		$this->assertEquals('fakepass', $pdo->getPassword());
	}

	/** @test */
	public function pdoOptionsAreReplaced()
	{
		$options = [
			PDO::ATTR_CASE               => PDO::CASE_LOWER,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];
		$config = [
			'dsn' => 'sqlite::memory:',
			'pdo_options' => $options
		];
		$pdo = $this->makeFactory()->makePdo($config);
		foreach ($options as $key => $value) {
			$this->assertEquals($value, $pdo->getAttribute($key));
		}
	}

	/** @test */
	public function pdoInitCommandsAreExecuted()
	{
		$config = [
			'dsn' => 'sqlite::memory:',
			'pdo_init_commands' => ['foo', 'bar'],
		];
		$pdo = $this->makeFactory($this->mockInstantiator())->makePdo($config);
		$this->assertEquals(['foo', 'bar'], $pdo->getExecLog());
	}

	/** @test */
	public function connectionFailureThrowsException()
	{
		$mock = $this->mockInstantiator();
		$mock->shouldReceive('instantiate')->andThrow($pe = new \PDOException('PDO exception message', 123));
		try {
			$pdo = $this->makeFactory($mock)->makePdo(['dsn' => 'sqlite::memory:']);
			$this->fail('No exception was thrown');
		} catch (Autarky\Database\CannotConnectException $e) {
			$this->assertEquals('PDO exception message', $e->getMessage());
			$this->assertEquals(123, $e->getCode());
			$this->assertSame($pe, $e->getPrevious());
		}
	}
}
