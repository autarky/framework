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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Autarky\Kernel\Application;

/**
 * Session middleware.
 */
class Middleware implements HttpKernelInterface
{
	/**
	 * @var HttpKernelInterface
	 */
	protected $kernel;

	/**
	 * @var SessionInterface
	 */
	protected $session;

	/**
	 * Whether the sessions should always be started. Normal behaviour is to
	 * start the sesion whenever an operation is requested from it.
	 *
	 * @var bool
	 */
	protected $forceStart;

	/**
	 * Additional cookie parameters to add to the session ID cookie.
	 *
	 * @var array
	 */
	protected $cookies;

	public function __construct(HttpKernelInterface $kernel, Application $app)
	{
		$this->kernel = $kernel;
		$this->session = $app->getContainer()
			->resolve('Symfony\Component\HttpFoundation\Session\SessionInterface');
		$this->forceStart = $app->getConfig()
			->get('session.force', false);
		$this->cookies = $app->getConfig()
			->get('session.cookies', []);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($type !== HttpKernelInterface::MASTER_REQUEST) {
			return $this->kernel->handle($request, $type, $catch);
		}

		$request->setSession($this->session);

		$cookies = $request->cookies;

		if ($cookies->has($this->session->getName())) {
			$this->session->setId($cookies->get($this->session->getName()));
		} else {
			$this->session->migrate(false);
		}

		if ($this->forceStart) {
			$this->session->start();
		}

		$response = $this->kernel->handle($request, $type, $catch);

		if ($this->session->isStarted()) {
			$this->session->save();

			$params = array_merge(session_get_cookie_params(), $this->cookies);

			if ($params['lifetime'] !== 0) {
				$params['lifetime'] = $request->server->get('REQUEST_TIME') + $params['lifetime'];
			}

			$cookie = new Cookie(
				$this->session->getName(),
				$this->session->getId(),
				$params['lifetime'],
				$params['path'],
				$params['domain'],
				$params['secure'],
				$params['httponly']
			);

			$response->headers->setCookie($cookie);
		}

		return $response;
	}
}
