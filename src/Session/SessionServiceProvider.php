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

use Autarky\Kernel\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->registerSessionHandler();
		$this->registerSessionStorage();
		$this->registerSession();
	}

	public function registerSessionHandler()
	{
		$this->app->getContainer()
		->share('SessionHandlerInterface', function ($container) {
			switch ($this->app->getConfig()->get('session.handler')) {
				case 'native':
					return new \SessionHandler;

				case 'file':
					$path = $this->app->getConfig()->get('path.session');

					return new NativeFileSessionHandler($path);

				case 'pdo':
					return new PdoSessionHandler($container->resolve('\PDO'),
						$this->app->getConfig()->get('session.handler-options'));

				case 'mongo':
					return new MongoDbSessionHandler($container->resolve('\MongoClient'),
						$this->app->getConfig()->get('session.handler-options'));

				case 'memcache':
					return new MemcacheSessionHandler($container->resolve('\Memcache'),
						$this->app->getConfig()->get('session.handler-options'));

				case 'memcached':
					return new MemcachedSessionHandler($container->resolve('\Memcached'),
						$this->app->getConfig()->get('session.handler-options'));

				case 'null':
					return new NullSessionHandler;

				default:
					throw new \RuntimeException('Unknown session handler type: '.
						$this->app->getConfig()->get('session.handler'));
			}
		});
	}

	public function registerSessionStorage()
	{
		$this->app->getContainer()
		->share('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface', function ($container) {
			if ($this->app->getConfig()->get('session.mock')) {
				return new MockArraySessionStorage;
			}
			$handler = $container['SessionHandlerInterface'];
			$options = $this->app->getConfig()->get('session.handler-options');

			return new NativeSessionStorage($options, $handler);
		});
	}

	public function registerSession()
	{
		$this->app->getContainer()
		->share('Symfony\Component\HttpFoundation\Session\Session', function ($container) {
			return new Session($container['Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface']);
		});
	}
}
