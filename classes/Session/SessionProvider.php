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
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\WriteCheckSessionHandler;

use Autarky\Providers\AbstractProvider;

/**
 * Service provider for symfony's session classes.
 */
class SessionProvider extends AbstractProvider
{
	/**
	 * @var \Autarky\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * @var \Autarky\Container\ContainerInterface
	 */
	protected $dic;

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->config = $this->app->getConfig();
		$this->dic = $this->app->getContainer();

		$this->dic->define('Autarky\Session\HandlerFactory', function() {
			return new HandlerFactory($this->dic, $this->config);
		});
		$this->dic->share('Autarky\Session\HandlerFactory');
		$this->dic->define('SessionHandlerInterface', [$this, 'makeSessionHandler']);
		$this->dic->share('SessionHandlerInterface');

		$this->dic->define('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface',
			[$this, 'makeSessionStorage']);
		$this->dic->share('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface');

		$this->dic->define('Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag', function() {
			return new AttributeBag('_autarky_attributes');
		});
		$this->dic->alias(
			'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag',
			'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface'
		);

		$this->dic->define('Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag', function() {
			return new AutoExpireFlashBag('_autarky_flashes');
		});
		$this->dic->alias(
			'Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag',
			'Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface'
		);

		$this->dic->define('Symfony\Component\HttpFoundation\Session\Session',
			[$this, 'makeSession']);
		$this->dic->share('Symfony\Component\HttpFoundation\Session\Session');
		$this->dic->alias(
			'Symfony\Component\HttpFoundation\Session\Session',
			'Symfony\Component\HttpFoundation\Session\SessionInterface'
		);

		$this->app->addMiddleware(['Autarky\Session\Middleware', $this->app]);
	}

	/**
	 * Make the session handler.
	 *
	 * @return \SessionHandlerInterface
	 */
	public function makeSessionHandler()
	{
		$handler = $this->dic->resolve('Autarky\Session\HandlerFactory')
			->makeHandler($this->config->get('session.handler'));

		if ($this->config->get('session.write_check') === true) {
			$handler = new WriteCheckSessionHandler($handler);
		}

		return $handler;
	}

	/**
	 * Make the session storage.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface
	 */
	public function makeSessionStorage()
	{
		$storage = $this->config->get('session.storage');

		if ($storage == 'mock_array') {
			return new MockArraySessionStorage;
		}
		if ($storage == 'mock_file') {
			return new MockFileSessionStorage;
		}

		$handler = $this->dic->resolve('SessionHandlerInterface');

		if ($storage == 'bridge') {
			return new PhpBridgeSessionStorage($handler);
		}

		$options = $this->config->get('session.storage_options', []);

		if ($storage == 'native') {
			return new NativeSessionStorage($options, $handler);
		}

		if (!is_string($storage)) {
			$storage = gettype($storage);
		}

		throw new \RuntimeException("Unknown session storage driver: $storage");
	}

	/**
	 * Make the session object.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	public function makeSession()
	{
		$session = new Session(
			$this->dic->resolve('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface'),
			$this->dic->resolve('Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface'),
			$this->dic->resolve('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface')
		);

		$session->setName($this->config->get('session.cookie.name', 'autarky_session'));

		return $session;
	}
}
