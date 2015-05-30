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

use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

use Autarky\Config\ConfigInterface;
use Autarky\Container\ContainerInterface;

/**
 * Session handler factory.
 *
 * @internal
 */
class HandlerFactory
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $factories;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $container
	 * @param ConfigInterface    $config
	 */
	public function __construct(
		ContainerInterface $container,
		ConfigInterface $config
	) {
		$this->container = $container;
		$this->config = $config;
		$this->factories = [
			'native'    => [$this, 'makeNativeHandler'],
			'file'      => [$this, 'makeFileHandler'],
			'pdo'       => [$this, 'makePdoHandler'],
			'mongo'     => [$this, 'makeMongoHandler'],
			'memcache'  => [$this, 'makeMemcacheHandler'],
			'memcached' => [$this, 'makeMemcachedHandler'],
			'null'      => [$this, 'makeNullHandler'],
		];
	}

	/**
	 * Define a handler factory.
	 *
	 * @param  string   $ident
	 * @param  callable $factory
	 *
	 * @return void
	 */
	public function defineFactory($ident, callable $factory)
	{
		$this->factories[$ident] = $factory;
	}

	/**
	 * Make a handler.
	 *
	 * @param  string $ident
	 *
	 * @return \SessionHandlerInterface
	 *
	 * @throws \InvalidArgumentException If ident is invalid
	 */
	public function makeHandler($ident)
	{
		if (!isset($this->factories[$ident])) {
			throw new \InvalidArgumentException("Unknown session handler: $ident");
		}

		return $this->factories[$ident]();
	}

	protected function makeNativeHandler()
	{
		return new \SessionHandler;
	}

	protected function makeFileHandler()
	{
		return new NativeFileSessionHandler($this->getSessionPath());
	}

	protected function makePdoHandler()
	{
		$pdo = $this->container->resolve('Autarky\Database\ConnectionManager')
			->getPdo($this->config->get('session.db_connection'));
		$options = $this->config->get('session.handler_options', []);
		return new PdoSessionHandler($pdo, $options);
	}

	protected function makeMongoHandler()
	{
		return new MongoDbSessionHandler($this->container->resolve('MongoClient'),
			$this->config->get('session.handler_options', []));
	}

	protected function makeMemcacheHandler()
	{
		return new MemcacheSessionHandler($this->container->resolve('Memcache'),
			$this->config->get('session.handler_options', []));
	}

	protected function makeMemcachedHandler()
	{
		return new MemcachedSessionHandler($this->container->resolve('Memcached'),
			$this->config->get('session.handler_options', []));
	}

	protected function makeNullHandler()
	{
		return new NullSessionHandler;
	}

	protected function getSessionPath()
	{
		if ($this->config->has('path.session')) {
			return $this->config->get('path.session');
		}

		if ($this->config->has('path.storage')) {
			return $this->config->get('path.storage').'/session';
		}

		return null;
	}
}
