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
	 * @var RouterInterface
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
	 * @param RouterInterface $router
	 * @param RequestStack    $requests
	 */
	public function __construct(RouterInterface $router, RequestStack $requests)
	{
		$this->router = $router;
		$this->requests = $requests;
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
	 * Get the URL to a named route.
	 *
	 * @param  string $name
	 * @param  array  $params
	 *
	 * @return string
	 */
	public function getRouteUrl($name, array $params = array(), $relative = false)
	{
		$path = $this->router->getRoute($name)
			->getPath($params);

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
