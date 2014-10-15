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
}
