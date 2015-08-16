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
// this must match the parser used in Route.php
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

use Autarky\Files\LockingFilesystem;

/**
 * FastRoute implementation of the router.
 */
class Router implements RouterInterface
{
	/**
	 * @var \Autarky\Routing\InvokerInterface
	 */
	protected $invoker;

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var \FastRoute\RouteCollector
	 */
	protected $routeCollector;

	/**
	 * @var \FastRoute\RouteParser
	 */
	protected $routeParser;

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
	 * @var \SplObjectStorage
	 */
	protected $routes;

	/**
	 * @var array
	 */
	protected $namedRoutes = [];

	/**
	 * @param InvokerInterface $invoker
	 * @param EventDispatcherInterface|null $eventDispatcher
	 * @param string|null $cachePath
	 */
	public function __construct(
		InvokerInterface $invoker,
		EventDispatcherInterface $eventDispatcher = null,
		$cachePath = null
	) {
		$this->routes = new \SplObjectStorage;
		$this->invoker = $invoker;
		$this->eventDispatcher = $eventDispatcher;

		if ($cachePath) {
			$this->cachePath = $cachePath;
			if (file_exists($cachePath)) {
				Route::setRouter($this);
				$this->dispatchData = require $cachePath;
				return;
			}
		}

		$this->routeCollector = new RouteCollector(
			$this->routeParser = new RouteParser, new DataGenerator
		);
	}

	/**
	 * @return bool
	 */
	public function isCaching()
	{
		return $this->dispatchData !== null;
	}

	/**
	 * @return \SplObjectStorage
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCurrentRoute()
	{
		return $this->currentRoute;
	}

	/**
	 * Add a "before" event listener.
	 *
	 * @param  string   $name
	 * @param  callable $handler
	 * @param  integer  $priority
	 *
	 * @return void
	 */
	public function onBefore($name, $handler, $priority = 0)
	{
		$this->addEventListener($name, $handler, 'before', $priority);
	}

	/**
	 * Add an "after" event listener.
	 *
	 * @param  string   $name
	 * @param  callable $handler
	 * @param  integer  $priority
	 *
	 * @return void
	 */
	public function onAfter($name, $handler, $priority = 0)
	{
		$this->addEventListener($name, $handler, 'after', $priority);
	}

	/**
	 * Add a global "before" event listener.
	 *
	 * @param  callable $handler
	 * @param  integer  $priority
	 *
	 * @return void
	 */
	public function globalOnBefore($handler, $priority = 0)
	{
		$this->addEventListener(null, $handler, 'before', $priority);
	}

	/**
	 * Add a global "after" event listener.
	 *
	 * @param  callable $handler
	 * @param  integer  $priority
	 *
	 * @return void
	 */
	public function globalOnAfter($handler, $priority = 0)
	{
		$this->addEventListener(null, $handler, 'after', $priority);
	}

	protected function addEventListener($name, $handler, $when, $priority)
	{
		if ($this->eventDispatcher === null) {
			return;
		}

		if ($name) {
			if (isset($this->filters[$name])) {
				throw new \LogicException("Filter with name $name already defined");
			}

			$this->filters[$name] = $name;
		}

		$name = $name ? "route.$when.$name" : "route.$when";
		$this->eventDispatcher->addListener($name, $handler, $priority);
	}

	/**
	 * {@inheritdoc}
	 */
	public function mount(array $routes, $path = '/')
	{
		if ($this->isCaching()) {
			return;
		}

		(new Configuration($this, $routes))
			->mount($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function group(array $flags, Closure $callback)
	{
		if ($this->isCaching()) {
			return;
		}

		$oldPrefix = $this->currentPrefix;
		$oldFilters = $this->currentFilters;

		foreach (['before', 'after'] as $when) {
			if (isset($flags[$when])) {
				foreach ((array) $flags[$when] as $filter) {
					$this->currentFilters[] = [$when, $this->getFilter($filter)];
				}
			}
		}

		if (isset($flags['prefix'])) {
			$this->currentPrefix .= '/' . trim($flags['prefix'], '/');
		}

		$callback($this);

		$this->currentPrefix = $oldPrefix;
		$this->currentFilters = $oldFilters;
	}

	protected function getFilter($name)
	{
		if (!isset($this->filters[$name])) {
			throw new \InvalidArgumentException("Filter with name $name is not defined");
		}

		return $this->filters[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRoute($methods, $path, $controller, $name = null)
	{
		if ($this->isCaching()) {
			return null;
		}

		$methods = (array) $methods;
		$path = $this->makePath($path);

		$route = $this->createRoute($methods, $path, $controller, $name);
		$this->routes->attach($route);

		if ($name) {
			$this->addNamedRoute($name, $route);
		}

		$this->routeCollector->addRoute($route->getMethods(), $path, $route);

		return $route;
	}

	/**
	 * Add a cached route.
	 *
	 * @param Route $route
	 *
	 * @internal
	 */
	public function addCachedRoute(Route $route)
	{
		$this->routes->attach($route);
		if ($name = $route->getName()) {
			$this->addNamedRoute($name, $route);
		}
	}

	protected function makePath($path)
	{
		if ($this->currentPrefix !== null) {
			$path = rtrim($this->currentPrefix, '/') .'/'. ltrim($path, '/');
		}

		if (substr($path, 0, 1) !== '/') {
			$path = '/' . $path;
		}

		if ($path == '/') {
			return $path;
		}

		return rtrim($path, '/');
	}

	protected function addNamedRoute($name, Route $route)
	{
		if (isset($this->namedRoutes[$name])) {
			throw new \InvalidArgumentException("Route with name $name already exists");
		}

		$this->namedRoutes[$name] = $route;
	}

	protected function createRoute($methods, $path, $controller, $name)
	{
		$route = new Route($methods, $path, $controller, $name);

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
		if (!isset($this->namedRoutes[$name])) {
			throw new \InvalidArgumentException("Route with name $name not found.");
		}

		return $this->namedRoutes[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(Request $request)
	{
		$route = $this->getRouteForRequest($request);

		return $this->getResponse($request, $route, $route->getParams());
	}

	/**
	 * Get the Route object corresponding to a given request.
	 *
	 * @param  Request $request
	 *
	 * @return Route
	 *
	 * @throws NotFoundHttpException
	 * @throws MethodNotAllowedHttpException
	 */
	public function getRouteForRequest(Request $request)
	{
		$method = $request->getMethod();
		$path = $request->getPathInfo() ?: '/';

		$result = $this->getDispatcher()
			->dispatch($method, $path);

		if ($result[0] == \FastRoute\Dispatcher::NOT_FOUND) {
			throw new NotFoundHttpException("No route match for path $path");
		} else if ($result[0] == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
			throw new MethodNotAllowedHttpException($result[1],
				"Method $method not allowed for path $path");
		} else if ($result[0] !== \FastRoute\Dispatcher::FOUND) {
			throw new \RuntimeException('Unknown result from FastRoute: '.$result[0]);
		}

		return $this->matchRoute($result[1], $result[2], $request);
	}

	protected function matchRoute(Route $route, array $params, Request $request)
	{
		$route->setParams($params);

		if ($this->eventDispatcher !== null) {
			$event = new Events\RouteMatchedEvent($request, $route);
			$this->eventDispatcher->dispatch('route.match', $event);
			$route = $event->getRoute();
		}

		return $route;
	}

	protected function getResponse(Request $request, Route $route, array $params)
	{
		// convert route params into container params
		$params = $this->getContainerParams($params, $request);

		$this->currentRoute = $route;

		if ($this->eventDispatcher !== null) {
			$event = new Events\BeforeFilterEvent($request, $route);
			$this->eventDispatcher->dispatch("route.before", $event);

			foreach ($route->getBeforeFilters() as $filter) {
				$this->eventDispatcher->dispatch("route.before.$filter", $event);
			}
		}

		// if the event has been dispatched, check if the event has a response
		// that should override the route's response. if the event doesn't have
		// a response, check if the event has a controller that should override
		// the route's controller
		if (isset($event) && !($response = $event->getResponse())) {
			$callable = $event->getController() ?: $route->getController();
		} else {
			$callable = $route->getController();
		}

		// if the event hasn't been dispatched, or the event hasn't had a
		// response set onto it, invoke the controller
		if (!isset($response) || !$response) {
			$response = $this->invoker->invoke($callable, $params);
		}

		// ensure that the response is a Response object before dispatching
		// after events and returning it
		if (!$response instanceof Response) {
			$response = new Response($response);
		}

		if ($this->eventDispatcher !== null) {
			$event = new Events\AfterFilterEvent($request, $route, $response);
			$this->eventDispatcher->dispatch("route.after", $event);

			foreach ($route->getAfterFilters() as $filter) {
				$this->eventDispatcher->dispatch("route.after.$filter", $event);
			}
		}

		$this->currentRoute = null;

		return $response;
	}

	protected function getContainerParams(array $routeParams, Request $request)
	{
		$params = [];

		// the container expects a dollar sign in front of non-class function
		// arguments
		foreach ($routeParams as $key => $value) {
			$params["\$$key"] = $value;
		}

		// this allows controllers to type-hint against the Request class to get
		// access to it directly
		$params['Symfony\Component\HttpFoundation\Request'] = $request;

		return $params;
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
			$filesys = new LockingFilesystem;
			$php = '<?php return '.var_export($data, true).";\n";
			$filesys->write($this->cachePath, $php);
		}

		return $data;
	}

	public function getRouteParser()
	{
		return $this->routeParser;
	}
}
