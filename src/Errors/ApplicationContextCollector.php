<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Errors;

use Autarky\Kernel\Application;

/**
 * {@inheritdoc}
 */
class ApplicationContextCollector implements ContextCollectorInterface
{
	/**
	 * @var Application
	 */
	protected $app;

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContext()
	{
		$request = $this->app->getRequestStack()->getCurrentRequest();
		$route = $this->app->getRouter()->getCurrentRoute();
		$routeName = ($route && $route->getName()) ? $route->getName() : 'No route';

		return [
			'method' => $request ? $request->getMethod() : null,
			'uri' => $request ? $request->getRequestUri() : null,
			'name' => $routeName,
		];
	}
}
