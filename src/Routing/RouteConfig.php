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

	public function override($name, array $routeData)
	{
		if (!isset($this->routes[$name])) {
			throw new \InvalidArgumentException("No route for name $name defined");
		}

		$this->routes[$name] = $routeData + $this->routes[$name];
	}

	public function merge(array $routes)
	{
		foreach ($routes as $name => $route) {
			$this->override($name, $route);
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
		foreach ($this->routes as $name => $route) {
			$path = $route['path'];
			$handler = $route['handler'];

			if (isset($route['methods'])) {
				$methods = (array) $route['methods'];
			} else if (isset($route['method'])) {
				$methods = (array) $route['method'];
			} else {
				$methods = ['GET'];
			}

			$this->router->addRoute($methods, $path, $handler, $name);
		}
	}
}
