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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

use Autarky\Events\EventDispatcherAwareInterface;
use Autarky\Events\EventDispatcherAwareTrait;

/**
 * FastRoute implementation of the router.
 */
class Router implements RouterInterface, EventDispatcherAwareInterface
{
	use EventDispatcherAwareTrait;

	/**
	 * @var \Autarky\Routing\InvokerInterface
	 */
	protected $invoker;

	/**
	 * @var \FastRoute\RouteCollector
	 */
	protected $routeCollector;

	/**
	 * @var mixed
	 */
	protected $dispatchData;

	/**
	 * @var string|null
	 */
	protected $cachePath;

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
	 * @var string
	 */
	protected $currentPrefix = '';

	/**
	 * @var array
	 */
	protected $filters = [];

	/**
	 * @var array
	 */
	protected $namedRoutes = [];

	/**
	 * @param InvokerInterface $invoker
	 * @param string|null      $cachePath
	 */
	public function __construct(InvokerInterface $invoker, $cachePath = null)
	{
		$this->invoker = $invoker;

		if ($cachePath) {
			$this->cachePath = $cachePath;
			if (file_exists($cachePath)) {
				Route::setRouter($this);
				$this->dispatchData = require $cachePath;
				return;
			}
		}

		$this->routeCollector = new RouteCollector(
			new RouteParser, new DataGenerator
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCurrentRoute()
	{
		return $this->currentRoute;
	}

	/**
	 * {@inheritdoc}
	 */
	public function defineFilter($name, $handler)
	{
		if (array_key_exists($name, $this->filters)) {
			throw new \LogicException("Filter with name $name already defined");
		}

		$this->filters[$name] = $handler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function mount(array $routes, $path = '/')
	{
		(new Configuration($this, $routes))
			->mount($path);
	}

	/**
	 * {@inheritdoc}
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
			$this->currentPrefix .= '/' . trim($flags['prefix'], '/');
		}

		$callback($this);

		$this->currentPrefix = $oldPrefix;
		$this->currentFilters = $oldFilters;
	}

	protected function getFilter($name)
	{
		if (!array_key_exists($name, $this->filters)) {
			throw new \InvalidArgumentException("Filter with name $name is not defined");
		}

		return $this->filters[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRoute($methods, $url, $handler, $name = null)
	{
		// if dispatchData is set, we're using cached data and routes can no
		// longer be added.
		if ($this->dispatchData !== null) {
			return null;
		}

		$methods = (array) $methods;
		$url = $this->makePath($url);

		$route = $this->createRoute($methods, $url, $handler, $name);

		if ($name) {
			$this->addNamedRoute($name, $route);
		}

		foreach ($methods as $method) {
			$this->routeCollector->addRoute(strtoupper($method), $url, $route);
		}

		return $route;
	}

	protected function makePath($path)
	{
		if ($this->currentPrefix !== null) {
			$path = rtrim($this->currentPrefix, '/') .'/'. ltrim($path, '/');
		}

		if (substr($path, 0, 1) !== '/') {
			$path = '/' . $path;
		}

		return rtrim($path, '/');
	}

	/**
	 * Add a named route to the router.
	 *
	 * @param string $name
	 * @param Route  $route
	 */
	public function addNamedRoute($name, Route $route)
	{
		if (array_key_exists($name, $this->namedRoutes)) {
			throw new \InvalidArgumentException("Route with name $name already exists");
		}

		$this->namedRoutes[$name] = $route;
	}

	protected function createRoute($methods, $url, $handler, $name)
	{
		$route = new Route($methods, $url, $handler, $name);

		foreach ($this->currentFilters as $filter) {
			$route->addFilter($filter[0], $filter[1]);
		}

		return $route;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRoute($name)
	{
		if (!array_key_exists($name, $this->namedRoutes)) {
			throw new \InvalidArgumentException("Route with name $name not found.");
		}

		return $this->namedRoutes[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(Request $request)
	{
		$this->currentRoute = null;

		$result = $this->getDispatcher()
			->dispatch($request->getMethod(), $request->getPathInfo());

		switch ($result[0]) {
			case \FastRoute\Dispatcher::FOUND:
				$args = [];
				foreach ($result[2] as $key => $value) {
					$args["\$$key"] = $value;
				}
				return $this->getResponse($request, $result[1], $args);

			case \FastRoute\Dispatcher::NOT_FOUND:
				throw new NotFoundHttpException('No route match for path '.$request->getPathInfo() ?: '/');

			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				throw new MethodNotAllowedHttpException($result[1], 'Method '.$request->getMethod()
					.' not allowed for path '.$request->getPathInfo() ?: '/');

			default:
				throw new \RuntimeException('Unknown result from FastRoute: '.$result[0]);
		}
	}

	protected function getResponse(Request $request, Route $route, array $args)
	{
		if ($this->eventDispatcher !== null) {
			$event = new Events\RouteMatchedEvent($request, $route);
			$this->eventDispatcher->dispatch('route.match', $event);
		}

		$this->currentRoute = $route;

		foreach ($route->getBeforeFilters() as $filter) {
			if ($response = $this->callFilter($filter, $route, $request)) {
				return $this->makeResponse($response);
			}
		}

		$response = $this->makeResponse($this->callRoute($route, $request, $args));

		foreach ($route->getAfterFilters() as $filter) {
			$this->callFilter($filter, $route, $request, $response);
		}

		return $response;
	}

	protected function makeResponse($result)
	{
		return $result instanceof Response ? $result : new Response($result);
	}

	protected function callRoute(Route $route, Request $request, array $args)
	{
		$args['Symfony\Component\HttpFoundation\Request'] = $request;

		return $this->invoker->invoke($route->getCallable(), $args);
	}

	protected function callFilter($filter, Route $route, Request $request, Response $response = null)
	{
		$params = [
			'Autarky\Routing\Route' => $route,
			'Symfony\Component\HttpFoundation\Request' => $request,
			'Symfony\Component\HttpFoundation\Response' => $response,
		];

		if (is_array($filter)) {
			$responder = $this->getCallable($filter[1], 'respond');
			$filter = $this->getCallable($filter[0], 'filter');
			$shouldRespond = $this->invoker->invoke($filter, $params);
			if ($shouldRespond) {
				return $this->invoker->invoke($responder, $params);
			}
		} else {
			$filter = $this->getCallable($filter, 'filter');
			return $this->invoker->invoke($filter, $params);
		}
	}

	protected function getCallable($callable, $defaultMethod)
	{
		if (is_string($callable) && !is_callable($callable)) {
			return \Autarky\splitclm($callable, $defaultMethod);
		}
		return $callable;
	}

	protected function getDispatcher()
	{
		if ($this->dispatchData !== null) {
			$dispatchData = $this->dispatchData;
		} else if ($this->routeCollector !== null) {
			$dispatchData = $this->generateDispatchData();
		} else {
			throw new \RuntimeException('No dipsatch data or route collector set');
		}

		return new Dispatcher($dispatchData);
	}

	protected function generateDispatchData()
	{
		$data = $this->routeCollector->getData();

		if ($this->cachePath !== null) {
			file_put_contents(
				$this->cachePath,
				'<?php return ' . var_export($data, true) . ';'
			);
		}

		return $data;
	}
}
