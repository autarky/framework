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
	/**
	 * @var \Autarky\Routing\Router
	 */
	protected static $router;

	/**
	 * @var array
	 */
	protected $methods;

	/**
	 * @var string
	 */
	protected $pattern;

	/**
	 * @var callable
	 */
	protected $controller;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var null|string
	 */
	protected $name;

	/**
	 * @var array|null
	 */
	protected $params = null;

	/**
	 * @param array    $methods    HTTP methods allowed for this route
	 * @param string   $pattern
	 * @param callable $controller
	 * @param string   $name
	 * @param array    $options
	 */
	public function __construct(array $methods, $pattern, $controller, $name = null, array $options = [])
	{
		$this->methods = array_map('strtoupper', $methods);
		$this->pattern = $pattern;
		$this->name = $name;
		$this->controller = $controller;
		$this->options = $options;
	}

	/**
	 * Get the methods the route responds to.
	 *
	 * @return string[]
	 */
	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * Get the URI pattern the route should match against.
	 *
	 * @return string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * Get the callable controller for the route.
	 *
	 * @return callable
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Get the route's name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the route options.
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Get a route option.
	 *
	 * @param  $option string
	 *
	 * @return mixed  Returns null if option not set.
	 */
	public function getOption($option)
	{
		return isset($this->options[$option]) ? $this->options[$option] : null;
	}

	/**
	 * Add a before hook.
	 *
	 * @param string $hook
	 */
	public function addBeforeHook($hook)
	{
		if (!isset($this->options['before'])) {
			$this->options['before'] = [];
		}
		$this->options['before'][] = $hook;
	}

	/**
	 * Add an after hook.
	 *
	 * @param string $hook
	 */
	public function addAfterHook($hook)
	{
		if (!isset($this->options['after'])) {
			$this->options['after'] = [];
		}
		$this->options['after'][] = $hook;
	}

	/**
	 * Add a before or after hook.
	 *
	 * @param string  $when   "before" or "after"
	 * @param string  $hook
	 */
	public function addHook($when, $hook)
	{
		$this->{'add'.ucfirst($when).'Hook'}($hook);
	}

	/**
	 * Get the route's before hooks.
	 *
	 * @return string[]
	 */
	public function getBeforeHooks()
	{
		return isset($this->options['before']) ? $this->options['before'] : [];
	}

	/**
	 * Get the route's after hooks.
	 *
	 * @return string[]
	 */
	public function getAfterHooks()
	{
		return isset($this->options['after']) ? $this->options['after'] : [];
	}

	/**
	 * When a match against the route has been confirmed, extract the parameters
	 * from the URI and pass them as an associative array to this method.
	 *
	 * @param array $params
	 */
	public function setParams(array $params)
	{
		$this->params = $params;
	}

	/**
	 * Get the parameters. Can only be called on a route that has been matched
	 * against an URI (i.e. setParams has been called)
	 *
	 * @return array
	 *
	 * @throws \BadMethodCallException If route has not been matched yet
	 */
	public function getParams()
	{
		if ($this->params === null) {
			throw new \BadMethodCallException("Cannot get params from a route that has not been matched yet");
		}

		return $this->params;
	}

	/**
	 * Set the router the route objects use.
	 *
	 * Somewhat of a hack to make var_export caching work.
	 *
	 * @param \Autarky\Routing\RouterInterface $router
	 */
	public static function setRouter(RouterInterface $router)
	{
		static::$router = $router;
	}

	/**
	 * Re-build a Route object from data that has been var_export-ed.
	 *
	 * @param  array $data
	 *
	 * @return static
	 */
	public static function __set_state($data)
	{
		$route = new static($data['methods'], $data['pattern'],
			$data['controller'], $data['name'], $data['options']);
		if (static::$router !== null) {
			static::$router->addCachedRoute($route);
		}
		return $route;
	}
}
