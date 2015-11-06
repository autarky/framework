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

use FastRoute\RouteParser;

class RoutePathGenerator implements RoutePathGeneratorInterface
{
	/**
	 * @var RouteParser
	 */
	protected $routeParser;

	/**
	 * Whether the regex pattern of route parameters should be validated on
	 * runtime.
	 *
	 * @var bool
	 */
	protected $validateParams;

	public function __construct(RouteParser $routeParser, $validateParams = false)
	{
		$this->routeParser = $routeParser;
		$this->validateParams = (bool) $validateParams;
	}

	/**
	 * Set whether the regex pattern of route parameters should be validated on
	 * runtime.
	 *
	 * @param bool $validateParams
	 */
	public function setValidateParams($validateParams)
	{
		$this->validateParams = (bool) $validateParams;
	}

	public function getRoutePath(Route $route, array $params)
	{
		$routes = $this->routeParser->parse($route->getPattern());

		foreach ($routes as $route) {
			$path = '';
			$index = 0;
			foreach ($route as $part) {
				// Fixed segment in the route
				if (is_string($part)) {
					$path .= $part;
					continue;
				}

				// Placeholder in the route
				if ($index === count($params)) {
					throw new \InvalidArgumentException('Too few parameters given');
				}

				if ($this->validateParams && $part[1] !== '[^/]+') {
					if (!preg_match("/^{$part[1]}$/", $params[$index])) {
						throw new \InvalidArgumentException("Route parameter pattern mismatch: "
							."Parameter #{$index} \"{$params[$index]}\" does not match pattern {$part[1]}");
					}
				}

				$path .= $params[$index++];
			}

			// If number of params in route matches with number of params given, use that route.
			// Otherwise try to find a route that has more params
			if ($index === count($params)) {
				return $path;
			}
		}

		throw new \InvalidArgumentException('Too many parameters given');
	}
}
