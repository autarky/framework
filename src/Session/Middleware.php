<?php
namespace Autarky\Session;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Autarky\Kernel\Application;

class Middleware implements HttpKernelInterface
{
	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->session = $app->resolve('Symfony\Component\HttpFoundation\Session\SessionInterface');
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
