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

use Autarky\Container\ContainerInterface;
use Autarky\Container\ContainerAwareInterface;
use Autarky\Events\EventDispatcherAwareInterface;

/**
 * FastRoute implementation of the router.
 */
class Router implements RouterInterface, EventDispatcherAwareInterface
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
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $eventDispatcher;

	public function __construct(ContainerInterface $container, $cachePath = null)
	{
		$this->container = $container;

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
	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
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

		if (substr($url, 0, 1) !== '/') {
			$url = '/'.$url;
		}

		$route = $this->createRoute($methods, $url, $handler, $name);

		if ($name) {
			$this->addNamedRoute($name, $route);
		}

		foreach ($methods as $method) {
			$this->routeCollector->addRoute(strtoupper($method), $url, $route);
		}

		return $route;
	}

	public function addNamedRoute($name, Route $route)
	{
		if (array_key_exists($name, $this->namedRoutes)) {
			throw new \InvalidArgumentException("Route with name $name already exists");
		}

		$this->namedRoutes[$name] = $route;
	}

	protected function createRoute($methods, $url, $handler, $name)
	{
		if ($this->currentPrefix !== null) {
			$url = rtrim($this->currentPrefix, '/') .'/'. ltrim($url, '/');
		}

		$url = rtrim($url, '/');

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
				return $this->makeResponse($this->getResult($request, $result[1], $result[2]));
				break;

			case \FastRoute\Dispatcher::NOT_FOUND:
				throw new NotFoundHttpException('No route match for path '.$request->getPathInfo() ?: '/');
				break;

			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				throw new MethodNotAllowedHttpException($result[1], 'Method '.$request->getMethod()
					.' not allowed for path '.$request->getPathInfo() ?: '/');
				break;

			default:
				throw new \RuntimeException('Unknown result from FastRoute: '.$result[0]);
				break;
		}
	}

	protected function getResult(Request $request, Route $route, array $args)
	{
		if ($this->eventDispatcher !== null) {
			$event = new Events\RouteMatchedEvent($request, $route);
			$this->eventDispatcher->dispatch('autarky.route-match', $event);
		}

		$this->currentRoute = $route;

		foreach ($route->getBeforeFilters() as $filter) {
			if ($result = $this->callFilter($filter, [$route, $request])) {
				return $result;
			}
		}

		$result = $route->run($request, $args, $this->container);

		foreach ($route->getAfterFilters() as $filter) {
			if ($afterResult = $this->callFilter($filter, [$route, $request, $result])) {
				return $afterResult;
			}
		}

		return $result;
	}

	protected function makeResponse($result)
	{
		return $result instanceof Response ? $result : new Response($result);
	}

	protected function callFilter($filter, array $args)
	{
		if ($filter instanceof Closure) {
			return call_user_func_array($filter, $args);
		}

		list($class, $method) = \Autarky\splitclm($filter, 'filter');

		$obj = $this->container->resolve($class);

		return call_user_func_array([$obj, $method], $args);
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
