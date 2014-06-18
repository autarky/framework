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

class RouteConfig
{
	protected $router;
	protected $routes = array();

	public function __construct(RouterInterface $router, array $routes = array())
	{
		$this->router = $router;
		$this->routes = $routes;
	}

	public function override($path, array $routeData)
	{
		if (!isset($this->routes[$path])) {
			throw new \InvalidArgumentException("No route for path $path defined");
		}

		$this->routes[$path] = $routeData + $this->routes[$path];
	}

	public function merge(array $routes)
	{
		foreach ($routes as $path => $route) {
			$this->override($path, $route);
		}
	}

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
		foreach ($this->routes as $path => $route) {
			if (isset($route['methods'])) {
				$methods = (array) $route['methods'];
			} else if (isset($route['method'])) {
				$methods = (array) $route['method'];
			} else {
				$methods = ['GET'];
			}

			$handler = $route['handler'];

			if (isset($route['name'])) {
				$name = $route['name'];
			} else {
				$name = null;
			}

			$this->router->addRoute($methods, $path, $handler, $name);
		}
	}
}
