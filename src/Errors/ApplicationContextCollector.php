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

class ApplicationContextCollector implements ContextCollectorInterface
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Get an array of context data for the application.
	 *
	 * @return array
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
