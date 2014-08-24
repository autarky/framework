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
	protected $app;
	protected $session;
	protected $forceStart;

	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->session = $app->getContainer()
			->resolve('Symfony\Component\HttpFoundation\Session\SessionInterface');
		$this->forceStart = $app->getConfig()
			->get('session.force', false);
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($type !== HttpKernelInterface::MASTER_REQUEST) {
			return $this->app->handle($request, $type, $catch);
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

		$response = $this->app->handle($request, $type, $catch);

		if ($this->session->isStarted()) {
			$this->session->save();

			$params = array_merge(
				session_get_cookie_params(),
				$this->app->getConfig()->get('session.cookie', [])
			);

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
