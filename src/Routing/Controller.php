<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Routing;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Autarky\Container\ContainerAware;

/**
 * Base web controller class to get newcomers started. It is recommended that
 * people write their own base-controllers instead, but this can be a good
 * starting point.
 */
abstract class Controller extends ContainerAware
{
	/**
	 * Render a template.
	 *
	 * @param  string $name Name of the view.
	 * @param  array  $data Data to pass to the view.
	 *
	 * @return string
	 */
	protected function view($name, array $data = array())
	{
		return $this->container->resolve('Autarky\Templating\TemplatingEngineInterface')
			->render($name, $data);
	}

	/**
	 * Generate the URL to a route.
	 *
	 * @param  string $name   Name of the route.
	 * @param  array  $params Route parameters.
	 *
	 * @return string
	 */
	protected function url($name, array $params = array())
	{
		return $this->container->resolve('Autarky\Routing\RouterInterface')
			->getRouteUrl($name, $params);
	}

	/**
	 * Get the session manager.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	protected function getSession()
	{
		return $this->container->resolve('Symfony\Component\HttpFoundation\Session\SessionInterface');
	}

	/**
	 * Create a response.
	 *
	 * @param  string  $content
	 * @param  integer $statusCode
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function response($content, $statusCode = 200)
	{
		return new Response($content, $statusCode);
	}

	/**
	 * Create a redirect response.
	 *
	 * @param  string $name   Name of the route to redirect to
	 * @param  array  $params Route parameters
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	protected function redirect($name, array $params = array())
	{
		return new RedirectResponse($this->url($name, $params));
	}

	/**
	 * Create a JSON response.
	 *
	 * @param  array   $data
	 * @param  integer $statusCode
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	protected function json(array $data, $statusCode = 200)
	{
		return new JsonResponse($data, $statusCode);
	}
}
