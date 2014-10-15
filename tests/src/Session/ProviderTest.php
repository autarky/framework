<?php
namespace Autarky\Tests\Session;

use Autarky\Tests\TestCase;
use Autarky\Session\SessionProvider;
use Mockery as m;

class ProviderTest extends TestCase
{
	/**
	 * @test
	 * @dataProvider getStorageData
	 */
	public function resolvesCorrectStorage($storage, $class)
	{
		$app = $this->makeApplication([new SessionProvider]);
		$app->getConfig()->set('session.handler', 'null');
		$app->getConfig()->set('session.storage', $storage);
		$app->boot();
		$this->assertInstanceOf($class, $app->resolve('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface'));
	}

	public function getStorageData()
	{
		return [
			['mock_array', 'Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage'],
			['mock_file', 'Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage'],
			['native', 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage'],
			['bridge', 'Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage'],
		];
	}

	/**
	 * @test
	 * @dataProvider getHandlerData
	 */
	public function resolvesCorrectHandler($handler, $class, $depends = null)
	{
		if ($depends) {
			if (!class_exists($depends)) {
				$this->markTestSkipped("Class $depends must be present for session handler $class to be resolved");
			}
		}
		$app = $this->makeApplication([new SessionProvider]);
		$app->getConfig()->set('session.handler', $handler);
		if ($handler == 'pdo') {
			$app->getConfig()->set('session.handler_options', ['db_table' => 'sessions']);
			$app->getConfig()->set('session.db_connection', 'default');
			$app->getConfig()->set('database.connections.default', ['dsn' => 'sqlite::memory:']);
		}
		if ($handler == 'mongo') {
			$app->getConfig()->set('session.handler_options', ['database' => 'autarky', 'collection' => 'sessions']);
			$app->getContainer()->define('MongoClient', function() {
				return new \MongoClient('mongodb://localhost:27017', ['connect' => false]);
			});
		}
		if ($handler == 'memcached') {
			$app->getContainer()->define('Memcached', function() { return new \Memcached(); });
		}
		$app->boot();
		$this->assertInstanceOf($class, $app->resolve('SessionHandlerInterface'));
	}

	public function getHandlerData()
	{
		return [
			['native', 'SessionHandler'],
			['file', 'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler'],
			['pdo', 'Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler'],
			['mongo', 'Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler', 'MongoClient'],
			['memcache', 'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler', 'Memcache'],
			['memcached', 'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler', 'Memcached'],
			['null', 'Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler'],
		];
	}

	/** @test */
	public function writeCheckHandler()
	{
		$app = $this->makeApplication([new SessionProvider]);
		$app->getConfig()->set('session.handler', 'null');
		$app->getConfig()->set('session.write_check', true);
		$app->boot();
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Storage\Handler\WriteCheckSessionHandler',
			$app->resolve('SessionHandlerInterface'));
	}
}
