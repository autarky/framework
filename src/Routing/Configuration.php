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

/**
 * Class representation of a routing config, which can be used instead of
 * manually adding routes onto the Router object.
 */
class Configuration
{
	/**
	 * @var RouterInterface
	 */
	protected $router;

	/**
	 * @var array
	 */
	protected $routes = array();

	/**
	 * @var string|null
	 */
	protected $namespace;

	/**
	 * @param RouterInterface $router
	 * @param array           $routes
	 * @param string|null     $namespace
	 */
	public function __construct(RouterInterface $router, array $routes, $namespace = null)
	{
		$this->router = $router;
		$this->routes = $routes;

		if ($namespace) {
			$this->namespace = $namespace;
		}
	}

	/**
	 * Override an existing route.
	 *
	 * @param  string $name
	 * @param  array  $routeData
	 *
	 * @return void
	 */
	public function override($name, array $routeData)
	{
		if (!array_key_exists($name, $this->routes)) {
			throw new \InvalidArgumentException("No route for name $name defined");
		}

		$this->routes[$name] = $routeData + $this->routes[$name];
	}

	/**
	 * Merge more routes into the configuration.
	 *
	 * @param  array  $routes
	 *
	 * @return void
	 */
	public function merge(array $routes)
	{
		foreach ($routes as $name => $route) {
			$this->override($name, $route);
		}
	}

	/**
	 * Mount the configuration.
	 *
	 * @param  string|null $prefix
	 *
	 * @return void
	 */
	public function mount($prefix = null)
	{
		if ($prefix) {
			$this->router->group(['prefix' => $prefix], function() {
				$this->registerRoutes();
			});
		} else {
			$this->registerRoutes();
		}
	}

	protected function registerRoutes()
	{
		foreach ($this->routes as $name => $route) {
			if ($this->namespace) {
				$name = $this->namespace . ':' . $name;
			}

			$path = $route['path'];
			$controller = isset($route['controller']) ?
				$route['controller'] : $route['handler'];

			if (array_key_exists('methods', $route)) {
				$methods = (array) $route['methods'];
			} else if (array_key_exists('method', $route)) {
				$methods = (array) $route['method'];
			} else {
				$methods = ['GET'];
			}

			$this->router->addRoute($methods, $path, $controller, $name);
		}
	}
}
