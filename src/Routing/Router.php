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

use Closure;
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
	 * @var \Autarky\Routing\Route
	 */
	protected $currentRoute;

	/**
	 * The filters that are currently applied to every route being added.
	 *
	 * @var array
	 */
	protected $currentFilters = [];

	/**
	 * The URL prefix that is currently applied to every route being added.
	 *
	 * @var null|string
	 */
	protected $currentPrefix;

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
	public function getCurrentRequest()
	{
		return $this->currentRequest;
	}

	public function setCurrentRequest(Request $request)
	{
		$this->currentRequest = $request;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCurrentRoute()
	{
		return $this->currentRoute;
	}

	/**
	 * Define a filter.
	 *
	 * @param  string $name
	 * @param  mixed  $handler Closure, or 'Class:method' string
	 *
	 * @return void
	 */
	public function defineFilter($name, $handler)
	{
		$this->filters[$name] = $handler;
	}

	/**
	 * Get a filter's handler by name.
	 *
	 * @param  string $name
	 *
	 * @return mixed
	 */
	public function getFilter($name)
	{
		if (!array_key_exists($name, $this->filters)) {
			throw new \InvalidArgumentException("Filter with name $name is not defined");
		}

		return $this->filters[$name];
	}

	/**
	 * Define a route group.
	 *
	 * @param  array   $flags    Valid keys are 'before', 'after', 'prefix'
	 * @param  Closure $callback First argument is the router ($this)
	 *
	 * @return void
	 */
	public function group(array $flags, Closure $callback)
	{
		$oldPrefix = $this->currentPrefix;
		$oldFilters = $this->currentFilters;

		foreach (['before', 'after'] as $when) {
			if (array_key_exists($when, $flags)) {
				foreach ((array) $flags[$when] as $filter) {
					$this->currentFilters[] = [$when, $this->getFilter($filter)];
				}
			}
		}

		if (array_key_exists('prefix', $flags)) {
			$this->currentPrefix = ($this->currentPrefix === null) ? $flags['prefix']
				: rtrim($this->currentPrefix, '/') .'/'. ltrim($flags['prefix'], '/');
		}

		$callback($this);

		$this->currentPrefix = $oldPrefix;
		$this->currentFilters = $oldFilters;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRoute($methods, $url, $handler, $name = null)
	{
		$methods = (array) $methods;

		if (substr($url, 0, 1) !== '/') {
			$url = '/'.$url;
		}

		$route = $this->createRoute($methods, $url, $handler, $name);

		if ($name !== null) {
			if (array_key_exists($name, $this->namedRoutes)) {
				throw new \InvalidArgumentException("Route with name $name already exists");
			}

			$this->namedRoutes[$name] = $route;
		}

		foreach ($methods as $method) {
			$this->routeCollector->addRoute(strtoupper($method), $url, $route);
		}

		return $route;
	}

	protected function createRoute($methods, $url, $handler, $name)
	{
		if ($this->currentPrefix !== null) {
			$url = rtrim($this->currentPrefix, '/') .'/'. ltrim($url, '/');
		}

		$route = new Route($methods, $url, $handler, $name);

		foreach ($this->currentFilters as $filter) {
			$route->addFilter($filter[0], $filter[1]);
		}

		return $route;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRouteUrl($name, array $params = array(), $relative = false)
	{
		$path = $this->getRoute($name)
			->getPath($params);

		if ($relative) {
			$root = $this->currentRequest->getBaseUrl();
		} else {
			$root = $this->getRootUrl();
		}

		return $root.$path;
	}

	public function getRootUrl()
	{
		$host = $this->currentRequest->getSchemeAndHttpHost();
		$base = $this->currentRequest->getBaseUrl();
		return rtrim($host.$base, '/');
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
		$this->currentRoute = null;

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
				throw new MethodNotAllowedHttpException($result[1], 'Method '.$request->getMethod().' not allowed for URL '.$request->getUri());
				break;

			default:
				throw new \RuntimeException('Unknown result from FastRoute: '.$result[0]);
				break;
		}
	}

	protected function getResult($route, $args)
	{
		$this->currentRoute = $route;

		$result = $route->run($args, $this->container);

		return $result instanceof Response ? $result : new Response($result);
	}

	protected function getDispatcher()
	{
		return new Dispatcher($this->routeCollector->getData());
	}
}
