<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container;

use Closure;

/**
 * Default implementation of the IoC container.
 */
class Container implements ContainerInterface
{
	protected $instances = [];
	protected $factories = [];
	protected $aliases = [];

	/**
	 * {@inheritdoc}
	 */
	public function bind($abstract, $concrete = null)
	{
		if ($concrete === null) {
			$concrete = $abstract;
		}

		$this->factories[$abstract] = $this->getFactory($concrete);
	}

	protected function getFactory($concrete)
	{
		if ($concrete instanceof Closure) {
			return $concrete;
		}

		return function($container) use($concrete) {
			return $this->build($concrete);
		};
	}

	/**
	 * {@inheritdoc}
	 */
	public function share($abstract, $concrete = null)
	{
		if ($concrete === null) {
			$concrete = $abstract;
		}

		if (is_string($concrete)) {
			$concrete = $this->getFactory($concrete);
		}

		if ($concrete instanceof Closure) {
			$this->factories[$abstract] = function($container) use($abstract, $concrete) {
				$result = $concrete($container);
				$this->instances[$abstract] = $result;
				return $result;
			};
		} else {
			$this->instances[$abstract] = $concrete;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve($abstract)
	{
		if (isset($this->aliases[$abstract])) {
			$abstract = $this->aliases[$abstract];
		}

		if (isset($this->instances[$abstract])) {
			return $this->instances[$abstract];
		}

		if (isset($this->factories[$abstract])) {
			$object = $this->factories[$abstract]($this);
		} else {
			$object = $this->build($abstract);
		}

		if ($object instanceof ContainerAwareInterface) {
			$object->setContainer($this);
		}

		return $object;
	}

	protected function build($class)
	{
		$reflClass = new \ReflectionClass($class);

		if (!$reflClass->hasMethod('__construct')) {
			return $reflClass->newInstance();
		}

		$args = [];
		$reflMethod = $reflClass->getMethod('__construct');

		foreach ($reflMethod->getParameters() as $reflParam) {
			if ($paramClass = $reflParam->getClass()) {
				$args[] = $this->resolve($paramClass->getName());
			} else {
				break;
			}
		}

		return $reflClass->newInstanceArgs($args);
	}

	/**
	 * {@inheritdoc}
	 */
	public function alias($alias, $target)
	{
		$this->aliases[$alias] = $target;
	}
}
