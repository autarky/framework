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

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

use Autarky\Kernel\ServiceProvider;
use Autarky\Container\ContainerInterface;

/**
 * Service provider for symfony's session classes.
 */
class SessionProvider extends ServiceProvider
{
	/**
	 * @var \Autarky\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->config = $this->app->getConfig();
		$container = $this->app->getContainer();

		$container->define('SessionHandlerInterface', [$this, 'makeSessionHandler']);
		$container->share('SessionHandlerInterface');

		$container->define('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface',
			[$this, 'makeSessionStorage']);
		$container->share('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface');

		$container->define('Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag', function() {
			return new AttributeBag('_autarky_attributes');
		});
		$container->alias(
			'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag',
			'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface'
		);

		$container->define('Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag', function() {
			return new AutoExpireFlashBag('_autarky_flashes');
		});
		$container->alias(
			'Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag',
			'Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface'
		);

		$container->define('Symfony\Component\HttpFoundation\Session\Session',
			[$this, 'makeSession']);
		$container->share('Symfony\Component\HttpFoundation\Session\Session');
		$container->alias(
			'Symfony\Component\HttpFoundation\Session\Session',
			'Symfony\Component\HttpFoundation\Session\SessionInterface'
		);

		$this->app->addMiddleware(['Autarky\Session\Middleware', $this->app]);
	}

	/**
	 * Make the session handler.
	 *
	 * @param  ContainerInterface $container
	 *
	 * @return \SessionHandlerInterface
	 */
	public function makeSessionHandler(ContainerInterface $container)
	{
		switch ($this->config->get('session.handler')) {
			case 'native':
				return new \SessionHandler;

			case 'file':
				if ($this->config->has('path.session')) {
					$path = $this->config->get('path.session');
				} else if ($this->config->has('path.storage')) {
					$path = $this->config->get('path.storage').'/session';
				} else {
					$path = null;
				}

				return new NativeFileSessionHandler($path);

			case 'pdo':
				$pdo = $container->resolve('Autarky\Database\MultiPdoContainer')
					->getPdo($this->config->get('session.db_connection'));
				$options = $this->config->get('session.handler_options', []);
				return new PdoSessionHandler($pdo, $options);

			case 'mongo':
				return new MongoDbSessionHandler($container->resolve('MongoClient'),
					$this->config->get('session.handler_options', []));

			case 'memcache':
				return new MemcacheSessionHandler($container->resolve('Memcache'),
					$this->config->get('session.handler_options', []));

			case 'memcached':
				return new MemcachedSessionHandler($container->resolve('Memcached'),
					$this->config->get('session.handler_options', []));

			case 'null':
				return new NullSessionHandler;

			default:
				throw new \RuntimeException('Unknown session handler type: '.
					$this->config->get('session.handler'));
		}
	}

	/**
	 * Make the session storage.
	 *
	 * @param  ContainerInterface $container
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface
	 */
	public function makeSessionStorage(ContainerInterface $container)
	{
		if ($this->config->get('session.mock') === true) {
			return new MockArraySessionStorage;
		}

		$options = $this->config->get('session.storage_options', []);
		$handler = $container->resolve('SessionHandlerInterface');

		return new NativeSessionStorage($options, $handler);
	}

	/**
	 * Make the session object.
	 *
	 * @param  ContainerInterface $container
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	public function makeSession(ContainerInterface $container)
	{
		$session = new Session(
			$container->resolve('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface'),
			$container->resolve('Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface'),
			$container->resolve('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface')
		);

		$session->setName($this->config->get('session.cookie.name', 'autarky_session'));

		return $session;
	}
}
