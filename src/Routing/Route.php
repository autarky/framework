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
use ReflectionMethod;
use ReflectionFunction;
use Symfony\Component\HttpFoundation\Request;

use Autarky\Container\ContainerInterface;

/**
 * Class that represents a single route in the application.
 */
class Route
{
	/**
	 * @var \Autarky\Routing\Router
	 */
	protected static $router;

	/**
	 * @var array
	 */
	protected $methods;

	/**
	 * @var string
	 */
	protected $pattern;

	/**
	 * @var string|\Closure
	 */
	protected $handler;

	/**
	 * @var null|string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $beforeFilters = [];

	/**
	 * @var array
	 */
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
		// for each regex match in $this->pattern, get the first param in
		// $params and replace the match with that
		$callback = function ($match) use (&$params, &$matches) {
			if (count($params) < 1) {
				throw new \InvalidArgumentException('Too few parameters given');
			}
			return array_shift($params);
		};

		$path = preg_replace_callback('/\{\w+\}/', $callback, $this->pattern);

		return $path;
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
	 * @see addFilter
	 */
	public function addBeforeFilter($filter)
	{
		$this->beforeFilters[] = $filter;
	}

	/**
	 * @see addFilter
	 */
	public function addAfterFilter($filter)
	{
		$this->afterFilters[] = $filter;
	}

	public function addFilter($when, $filter)
	{
		$this->{'add'.ucfirst($when).'Filter'}($filter);
	}

	public function getBeforeFilters()
	{
		return $this->beforeFilters;
	}

	public function getAfterFilters()
	{
		return $this->afterFilters;
	}

	/**
	 * Run the route.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param array                                     $args
	 * @param \Autarky\Container\ContainerInterface     $container
	 *
	 * @return mixed
	 */
	public function run(Request $request = null, array $args = array(), ContainerInterface $container = null)
	{
		if ($this->handler instanceof Closure) {
			$callable = $this->handler;
		} else {
			list($class, $method) = \Autarky\splitclm($this->handler, 'action');

			$obj = $container ? $container->resolve($class) : new $class;
			$callable = [$obj, $method];
		}

		if ($request) {
			$this->addRequestToArgs($args, $callable, $request);
		}

		return call_user_func_array($callable, $args);
	}

	protected function addRequestToArgs(array &$args, callable $callable, Request $request)
	{
		if (is_array($callable)) {
			$refl = new ReflectionMethod($callable[0], $callable[1]);
		} else {
			$refl = new ReflectionFunction($callable);
		}

		$params = $refl->getParameters();

		if (empty($params)) return;

		$paramClass = $params[0]
			->getClass();

		if (!$paramClass) return;

		if (
			$paramClass->isSubclassOf('Symfony\Component\HttpFoundation\Request') ||
			$paramClass->getName() == 'Symfony\Component\HttpFoundation\Request'
		) {
			array_unshift($args, $request);
		}
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
		if (static::$router !== null && array_key_exists('name', $data) && $data['name']) {
			static::$router->addNamedRoute($data['name'], $route);
		}
		return $route;
	}
}
