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

	/**
	 * Add a filter to the route.
	 *
	 * @param string $when   'before' or 'after'
	 * @param mixed  $filter callable or 'Class:method'
	 */
	public function addFilter($when, $filter)
	{
		$this->{$when.'Filters'}[] = $filter;
	}

	/**
	 * @see addFilter
	 */
	public function addBeforeFilter($filter)
	{
		return $this->addFilter('before', $filter);
	}

	/**
	 * @see addFilter
	 */
	public function addAfterFilter($filter)
	{
		return $this->addFilter('after', $filter);
	}

	/**
	 * Call the route's filters.
	 *
	 * @param  string                                $when      'before' or 'after'
	 * @param  array                                 $args
	 * @param  \Autarky\Container\ContainerInterface $container
	 *
	 * @return mixed
	 */
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

	/**
	 * Run the route.
	 *
	 * @param  array                                 $args
	 * @param  \Autarky\Container\ContainerInterface $container
	 *
	 * @return mixed
	 */
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

	/**
	 * Get the result from a handler.
	 *
	 * @param  mixed                                 $handler
	 * @param  array                                 $args
	 * @param  \Autarky\Container\ContainerInterface $container
	 * @param  string                                $action
	 *
	 * @return mixed
	 */
	protected function getHandlerResult($handler, array $args, ContainerInterface $container = null, $action = 'action')
	{
		if ($handler instanceof Closure) {
			return call_user_func_array($handler, $args);
		}

		list($class, $method) = \Autarky\splitclm($handler, $action);

		$obj = $container ? $container->resolve($class) : new $class;

		return call_user_func_array([$obj, $method], $args);
	}

	/**
	 * Set the router the route objects use.
	 *
	 * Somewhat of a hack to make var_export caching work.
	 *
	 * @param \Autarky\Routing\RouterInterface $router
	 */
	public static function setRouter(RouterInterface $router)
	{
		static::$router = $router;
	}

	/**
	 * Re-build a Route object from data that has been var_export-ed.
	 *
	 * @param  array $data
	 *
	 * @return static
	 */
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
