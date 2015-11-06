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
	 * @var RoutePathGeneratorInterface
	 */
	protected $routePathGenerator;

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
	 * @param Router                      $router
	 * @param RoutePathGeneratorInterface $routePathGenerator
	 * @param RequestStack                $requests
	 * @param bool                        $validateParams
	 */
	public function __construct(
		Router $router,
		RoutePathGenerator $routePathGenerator,
		RequestStack $requests,
		$validateParams = false
	) {
		$this->router = $router;
		$this->routePathGenerator = $routePathGenerator;
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
		$this->routePathGenerator->setValidateParams($validateParams);
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

		$path = $this->routePathGenerator->getRoutePath($route, $routeParams);

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
