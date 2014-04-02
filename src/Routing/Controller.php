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

abstract class Controller extends ContainerAware
{
	protected function view($name, array $data = array())
	{
		return $this->container->resolve('\Autarky\Templating\TemplatingEngineInterface')
			->render($name, $data);
	}

	protected function url($name, array $params = array())
	{
		return $this->container->resolve('\Autarky\Routing\RouterInterface')
			->getRouteUrl($name, $params);
	}

	protected function getSession()
	{
		return $this->container->resolve('\Symfony\Component\HttpFoundation\Session\Session');
	}

	protected function response($content, $statusCode = 200)
	{
		return new Response($content, $statusCode);
	}

	protected function redirect($name, array $params = array())
	{
		return new RedirectResponse($this->url($name, $params));
	}

	protected function json(array $data, $statusCode = 200)
	{
		return new JsonResponse($data, $statusCode);
	}
}
