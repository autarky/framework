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
	 * @var callable
	 */
	protected $controller;

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
	 * @param array    $methods    HTTP methods allowed for this route
	 * @param string   $pattern
	 * @param callable $controller
	 * @param string   $name
	 */
	public function __construct(array $methods, $pattern, $controller, $name = null)
	{
		$this->methods = $methods;
		$this->pattern = $pattern;
		$this->name = $name;

		if (is_string($controller) && !is_callable($controller)) {
			$controller = \Autarky\splitclm($controller, 'action');
		}

		$this->controller = $controller;
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
		$callback = function () use (&$params, &$matches) {
			if (count($params) < 1) {
				throw new \InvalidArgumentException('Too few parameters given');
			}
			return array_shift($params);
		};

		$path = preg_replace_callback('/\{\w+\}/', $callback, $this->pattern);

		if (count($params) > 0) {
			$path .= '?';
			$path .= http_build_query($params);
		}

		return $path;
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
	 * Add a before filter.
	 *
	 * @param string $filter
	 */
	public function addBeforeFilter($filter)
	{
		$this->beforeFilters[] = $filter;
	}

	/**
	 * Add an after filter.
	 *
	 * @param string $filter
	 */
	public function addAfterFilter($filter)
	{
		$this->afterFilters[] = $filter;
	}

	/**
	 * Add a before or after filter.
	 *
	 * @param string  $when   "before" or "after"
	 * @param string  $filter
	 */
	public function addFilter($when, $filter)
	{
		$this->{'add'.ucfirst($when).'Filter'}($filter);
	}

	/**
	 * Get the route's before filters.
	 *
	 * @return string[]
	 */
	public function getBeforeFilters()
	{
		return $this->beforeFilters;
	}

	/**
	 * Get the route's after filters.
	 *
	 * @return string[]
	 */
	public function getAfterFilters()
	{
		return $this->afterFilters;
	}

	public function getController()
	{
		return $this->controller;
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
