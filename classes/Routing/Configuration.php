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
		if (!isset($this->routes[$name])) {
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

			$this->registerRoute($route, $name);
		}
	}

	protected function registerRoute($route, $name)
	{
		$path = $route['path'];

		if (isset($route['methods'])) {
			$methods = (array) $route['methods'];
		} elseif (isset($route['method'])) {
			$methods = (array) $route['method'];
		} else {
			$methods = ['GET'];
		}

		$options = [];
		if (isset($route['params'])) {
			$options['params'] = $route['params'];
		}
		if (isset($route['constructor_params'])) {
			$options['constructor_params'] = $route['constructor_params'];
		}

		// it is possible to provide a subarray to 'methods', which means the same
		// route will serve multiple HTTP methods, but with different controllers
		// for each method. if that is the case, iterate through the methods and
		// add the route for each of them.
		if (array_filter(array_keys($methods), 'is_string')) {
			foreach ($methods as $method => $controller) {
				$this->router->addRoute([$method], $path, $controller, $name, $options);

				// only set the name for the first route added. the URLs will be
				// identical so it doesn't matter for URL generation.
				$name = null;
			}
		} else {
			// only a single method is being mapped
			$controller = isset($route['controller']) ?
				$route['controller'] : $route['handler'];
			$this->router->addRoute($methods, $path, $controller, $name, $options);
		}
	}
}
