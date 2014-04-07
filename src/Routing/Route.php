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
 * Class that represents a single route in the application.
 */
class Route
{
	protected $methods;
	protected $pattern;
	protected $handler;
	protected $name;

	/**
	 * @param array  $methods HTTP methods allowed for this route
	 * @param string $pattern
	 * @param mixed  $handler
	 * @param string $name
	 */
	public function __construct(array $methods, $pattern, $handler, $name = null)
	{
		$this->methods = $methods;
		$this->pattern = $pattern;
		$this->handler = $handler;
		$this->name = $name;
	}

	/**
	 * Given a set of parameters, get the relative path to the route.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function getPath(array $params)
	{
		// for each regex match in $this->pattern, get the first param in
		// $params and replace the match with that
		return preg_replace_callback('/\{\w+\}/', function ($match) use (&$params) {
			return array_shift($params);
		}, $this->pattern);
	}
}
