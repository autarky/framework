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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

use Autarky\Container\ContainerInterface;
use Autarky\Container\ContainerAwareInterface;

/**
 * FastRoute implementation of the router.
 */
class Router implements RouterInterface
{
	/**
	 * @var \Autarky\Container\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \FastRoute\RouteCollector
	 */
	protected $routeCollector;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $currentRequest;

	/**
	 * @var array
	 */
	protected $namedRoutes = [];

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->routeCollector = new RouteCollector(
			new RouteParser, new DataGenerator
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRoute($methods, $url, $handler, $name = null)
	{
		$methods = (array) $methods;

		$route = new Route($methods, $url, $handler, $name);

		if ($name !== null) {
			if (array_key_exists($name, $this->namedRoutes)) {
				throw new \InvalidArgumentException("Route with name $name already exists");
			}

			$this->namedRoutes[$name] = $route;
		}

		foreach ($methods as $method) {
			$this->routeCollector->addRoute($method, $url, $handler);
		}

		return $route;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRouteUrl($name, array $params = array())
	{
		$path = $this->getRoute($name)
			->getPath($params);

		return rtrim(
			$this->currentRequest->getSchemeAndHttpHost() .
			$this->currentRequest->getBaseUrl(), '/') .
			$path;
	}

	protected function getRoute($name)
	{
		if (!array_key_exists($name, $this->namedRoutes)) {
			throw new RouteNotFoundException("Route with name $name not found.");
		}

		return $this->namedRoutes[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(Request $request)
	{
		$this->currentRequest = $request;

		$result = $this->getDispatcher()
			->dispatch($request->getMethod(), $request->getPathInfo());

		switch ($result[0]) {
			case \FastRoute\Dispatcher::FOUND:
				// add the request as the first parameter
				array_unshift($result[2], $request);

				return $this->getResult($result[1], $result[2]);
				break;

			case \FastRoute\Dispatcher::NOT_FOUND:
				throw new NotFoundHttpException('No route match for URL '.$request->getUri());
				break;

			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				throw new MethodNotAllowedHttpException('Method '.$request->getMethod().' not allowed for URL '.$request->getUrl());
				break;

			default:
				throw new \RuntimeException('Unknown result from FastRoute: '.$result[0]);
				break;
		}
	}

	protected function getResult($callback, $args)
	{
		if ($callback instanceof Closure) {
			return $callback();
		}

		list($class, $method) = explode(':', $callback);
		$obj = $this->container->resolve($class);

		$result = call_user_func_array([$obj, $method], $args);
		return $result instanceof Response ? $result : new Response($result);
	}

	protected function getDispatcher()
	{
		return new Dispatcher($this->routeCollector->getData());
	}
}
