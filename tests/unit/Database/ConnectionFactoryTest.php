<?php

class ConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
	private function makeFactory()
	{
		return new Autarky\Database\ConnectionFactory;
	}

	/** @test */
	public function getConnectionWithDsn()
	{
		$config = ['dsn' => 'sqlite::memory:'];
		$pdo = $this->makeFactory()->makePdo($config);
		$this->assertInstanceOf('PDO', $pdo);
	}

	/** @test */
	public function getConnectionWithoutDsn()
	{
		$config = ['driver' => 'sqlite', 'path' => ':memory:'];
		$pdo = $this->makeFactory()->makePdo($config);
		$this->assertInstanceOf('PDO', $pdo);
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
}
