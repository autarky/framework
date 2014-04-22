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
use Autarky\Container\ContainerInterface;

/**
 * Class that represents a single route in the application.
 */
class Route
{
	protected static $router;

	protected $methods;
	protected $pattern;
	protected $handler;
	protected $name;
	protected $beforeFilters = [];
	protected $afterFilters = [];

	/**
	 * @param array  $methods HTTP methods allowed for this route
	 * @param string $pattern
	 * @param mixed  $handler
	 * @param string $name
	 */
	public function __construct(array $methods, $pattern, $handler, $name = null)
	{
		$this->methods = $methods;
		$this->pattern = $pattern;
		$this->handler = $handler;
		$this->name = $name;
	}

	/**
	 * Given a set of parameters, get the relative path to the route.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function getPath(array $params = array())
	{
		if (empty($params)) return $this->pattern;

		// for each regex match in $this->pattern, get the first param in
		// $params and replace the match with that
		return preg_replace_callback('/\{\w+\}/', function ($match) use (&$params) {
			return array_shift($params);
		}, $this->pattern);
	}

	/**
	 * Get the handler for the route.
	 *
	 * @return \Closure|string
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Get the route's name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	public function addFilter($when, $filter)
	{
		$this->{$when.'Filters'}[] = $filter;
	}

	public function addBeforeFilter($filter)
	{
		return $this->addFilter('before', $filter);
	}

	public function addAfterFilter($filter)
	{
		return $this->addFilter('after', $filter);
	}

	public function callFilters($when, array $args, ContainerInterface $container = null)
	{
		// add $this as first argument to all filters
		array_unshift($args, $this);

		$filters = $this->{$when.'Filters'};

		if (empty($filters)) {
			return;
		}

		foreach ($filters as $filter) {
			$result = $this->getHandlerResult($filter, $args, $container, 'filter');
			if ($result !== null) return $result;
		}
	}

	public function run(array $args = array(), ContainerInterface $container = null)
	{
		if ($result = $this->callFilters('before', $args, $container)) {
			return $result;
		}

		$result = $this->getHandlerResult($this->handler, $args, $container, 'action');

		if ($afterResult = $this->callFilters('after', (array) $result, $container)) {
			return $afterResult;
		}

		return $result;
	}

	protected function getHandlerResult($handler, array $args, ContainerInterface $container = null)
	{
		if ($handler instanceof Closure) {
			return call_user_func_array($handler, $args);
		}

		list($class, $method) = \Autarky\splitclm($listener, $action);

		$obj = $container ? $container->resolve($class) : new $class;

		return call_user_func_array([$obj, $method], $args);
	}

	public static function setRouter(RouterInterface $router)
	{
		static::$router = $router;
	}

	public static function __set_state($data)
	{
		$route = new static($data['methods'], $data['pattern'], $data['handler'], $data['name']);
		$route->beforeFilters = $data['beforeFilters'];
		$route->afterFilters = $data['afterFilters'];
		if (isset(static::$router) && $data['name']) {
			static::$router->addNamedRoute($data['name'], $route);
		}
		return $route;
	}
}
