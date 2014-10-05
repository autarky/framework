<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;

use Autarky\Kernel\ServiceProvider;
use Autarky\Container\ContainerInterface;

/**
 * Service provider for symfony's session classes.
 */
class SessionProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerSessionHandler();
		$this->registerSessionStorage();
		$this->registerSession();
		$this->registerMiddleware();
	}

	protected function registerSessionHandler()
	{
		$this->app->getContainer()->define(
			'SessionHandlerInterface',
			function (ContainerInterface $container) {
				switch ($this->app->getConfig()->get('session.handler')) {
					case 'native':
						return new \SessionHandler;

					case 'file':
						$path = $this->app->getConfig()->get('path.session');
						return new NativeFileSessionHandler($path);

					case 'pdo':
						$connection = $this->app->getConfig()->get('session.db-connection');
						$pdo = $container->resolve('Autarky\Database\MultiPdoContainer')
							->getPdo($connection);
						$options = $this->app->getConfig()->get('session.handler-options', []);
						return new PdoSessionHandler($pdo, $options);

					case 'mongo':
						return new MongoDbSessionHandler($container->resolve('MongoClient'),
							$this->app->getConfig()->get('session.handler-options', []));

					case 'memcache':
						return new MemcacheSessionHandler($container->resolve('Memcache'),
							$this->app->getConfig()->get('session.handler-options', []));

					case 'memcached':
						return new MemcachedSessionHandler($container->resolve('Memcached'),
							$this->app->getConfig()->get('session.handler-options', []));

					case 'null':
						return new NullSessionHandler;

					default:
						throw new \RuntimeException('Unknown session handler type: '.
							$this->app->getConfig()->get('session.handler'));
				}
			});
		$this->app->getContainer()
			->share('SessionHandlerInterface');
	}

	protected function registerSessionStorage()
	{
		$this->app->getContainer()->define(
			'Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface',
			function (ContainerInterface $container) {
				if ($this->app->getConfig()->get('session.mock')) {
					return new MockArraySessionStorage;
				}

				$options = $this->app->getConfig()->get('session.storage-options', []);
				$handler = $container->resolve('SessionHandlerInterface');

				return new NativeSessionStorage($options, $handler);
			});
		$this->app->getContainer()
			->share('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface');
	}

	protected function registerSession()
	{
		$this->app->getContainer()
			->share('Symfony\Component\HttpFoundation\Session\Session');
		$this->app->getContainer()->define(
			'Symfony\Component\HttpFoundation\Session\Session',
			function (ContainerInterface $container) {
				$session = new Session(
					$container->resolve('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface'),
					null,
					new AutoExpireFlashBag()
				);
				$session->setName($this->app->getConfig()->get('session.cookie.name', 'autarky_session'));
				return $session;
			});

		$this->app->getContainer()->alias(
			'Symfony\Component\HttpFoundation\Session\Session',
			'Symfony\Component\HttpFoundation\Session\SessionInterface'
		);
	}

	protected function registerMiddleware()
	{
		$this->app->addMiddleware([__NAMESPACE__.'\Middleware', $this->app]);
	}
}
