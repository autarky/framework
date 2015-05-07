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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Autarky\Application;

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

	/**
	 * @param HttpKernelInterface $kernel
	 * @param Application         $app
	 */
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
		// always set the session onto the request object.
		$request->setSession($this->session);

		// we only need to manage the session for the master request.
		// subrequests will have the session available anyways, but we will
		// be closing and setting the cookie for the master request only.
		if ($type !== HttpKernelInterface::MASTER_REQUEST) {
			return $this->kernel->handle($request, $type, $catch);
		}

		// the session may have been manually started before the middleware is
		// invoked - in this case, we cross our fingers and hope the session has
		// properly initialised itself
		if (!$this->session->isStarted()) {
			$this->initSession($request);
		}

		$response = $this->kernel->handle($request, $type, $catch);

		// if the session has started, save it and attach the session cookie. if
		// the session has not started, there is nothing to save and there is no
		// point in attaching a cookie to persist it.
		if ($this->session->isStarted()) {
			$this->closeSession($request, $response);
		}

		return $response;
	}

	protected function initSession(Request $request)
	{
		// the name of the session cookie name.
		$sessionName = $this->session->getName();

		// if a session cookie exists, load the appropriate session ID.
		if ($request->cookies->has($sessionName)) {
			$this->session->setId($request->cookies->get($sessionName));
		}

		// in some rare cases you may want to force the session to start on
		// every request.
		if ($this->forceStart) {
			$this->session->start();
		}
	}

	protected function closeSession(Request $request, Response $response)
	{
		// save all session data
		$this->session->save();

		// attach the session cookie
		$response->headers->setCookie($this->makeCookie($request));
	}

	protected function makeCookie(Request $request)
	{
		// merge native PHP session cookie params with custom ones.
		$params = array_replace(session_get_cookie_params(), $this->cookies);

		// if the cookie lifetime is not 0 (closes when browser window closes),
		// add the request time and the lifetime to get the expiration time of
		// the cookie.
		if ($params['lifetime'] !== 0) {
			$params['lifetime'] = $request->server->get('REQUEST_TIME') + $params['lifetime'];
		}

		return new Cookie(
			$this->session->getName(),
			$this->session->getId(),
			$params['lifetime'],
			$params['path'],
			$params['domain'],
			$params['secure'],
			$params['httponly']
		);
	}
}
