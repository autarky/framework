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

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * URL generator.
 */
class UrlGenerator
{
	/**
	 * @var Router
	 */
	protected $router;

	/**
	 * @var RequestStack
	 */
	protected $requests;

	/**
	 * The root URL to use for assets, if any.
	 *
	 * @var null|string
	 */
	protected $assetRoot;

	/**
	 * Whether the regex pattern of route parameters should be validated on
	 * runtime.
	 *
	 * @var bool
	 */
	protected $validateParams;

	/**
	 * @param Router       $router
	 * @param RequestStack $requests
	 * @param bool         $validateParams
	 */
	public function __construct(
		Router $router,
		RequestStack $requests,
		$validateParams = false
	) {
		$this->router = $router;
		$this->requests = $requests;
		$this->validateParams = (bool) $validateParams;
	}

	/**
	 * Set the root URL for assets. Useful if you're using CDNs.
	 *
	 * @param string $assetRoot
	 */
	public function setAssetRoot($assetRoot)
	{
		$this->assetRoot = rtrim($assetRoot, '/');
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

	/**
	 * Get the URL to a named route.
	 *
	 * @param  string $name
	 * @param  array  $params
	 * @param  bool   $relative
	 *
	 * @return string
	 */
	public function getRouteUrl($name, array $params = array(), $relative = false)
	{
		$route = $this->router->getRoute($name);

		$routeParams = [];
		$query = [];
		foreach ($params as $key => $value) {
			if (is_int($key)) {
				$routeParams[] = $value;
			} else {
				$query[$key] = $value;
			}
		}

		$path = $this->getRoutePath($route, $routeParams);

		if ($query) {
			$path .= '?'.http_build_query($query);
		}

		if ($relative) {
			$root = $this->requests->getCurrentRequest()
				->getBaseUrl();
		} else {
			$root = $this->getRootUrl();
		}

		return $root . $path;
	}

	protected function getRoutePath(Route $route, array $params)
	{
		$routes = $this->router->getRouteParser()->parse($route->getPattern());

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

	/**
	 * Get the URL to an asset.
	 *
	 * @param  string  $path
	 * @param  boolean $relative
	 *
	 * @return string
	 */
	public function getAssetUrl($path, $relative = false)
	{
		if (substr($path, 0, 1) !== '/') {
			$path = '/'.$path;
		}

		if ($this->assetRoot !== null) {
			$base = $this->assetRoot;
		} else if ($relative) {
			$base = $this->requests
				->getCurrentRequest()
				->getBaseUrl();
		} else {
			$base = $this->getRootUrl();
		}

		return $base . $path;
	}

	/**
	 * Get the root URL. Used to generate URLs to assets.
	 *
	 * @return string
	 */
	public function getRootUrl()
	{
		$request = $this->requests->getCurrentRequest();
		$host = $request->getHttpHost();
		$base = $request->getBaseUrl();

		return rtrim("//$host/$base", '/');
	}
}
