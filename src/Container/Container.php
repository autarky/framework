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
use ReflectionClass;
use ReflectionException;

/**
 * Default implementation of the IoC container.
 */
class Container implements ContainerInterface
{
	/**
	 * Resolved instances.
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Factories.
	 *
	 * @var \Closure[]
	 */
	protected $factories = [];

	/**
	 * Aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * Resolving callbacks.
	 *
	 * @var array
	 */
	protected $resolvingCallbacks = [];
	protected $resolvingAnyCallbacks = [];

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
			$this->callResolvingCallbacks($abstract, $concrete);
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

		$this->callResolvingCallbacks($abstract, $object);

		return $object;
	}

	protected function callResolvingCallbacks($key, $object)
	{
		foreach ($this->resolvingAnyCallbacks as $callback) {
			$callback($object, $this);
		}

		if (isset($this->resolvingCallbacks[$key])) {
			foreach ($this->resolvingCallbacks[$key] as $callback) {
				$callback($object, $this);
			}
		}
	}

	protected function build($class)
	{
		$reflClass = new ReflectionClass($class);

		if (!$reflClass->isInstantiable()) {
			throw new NotInstantiableException("Class $class is not instantiable");
		}

		if (!$reflClass->hasMethod('__construct')) {
			return $reflClass->newInstance();
		}

		$args = [];
		$reflMethod = $reflClass->getMethod('__construct');

		foreach ($reflMethod->getParameters() as $reflParam) {
			if (!$paramClass = $reflParam->getClass()) {
				if ($reflParam->isDefaultValueAvailable()) {
					$args[] = $reflParam->getDefaultValue();
				} else {
					throw new UnresolvableDependencyException('Unresolvable dependency: '
						.'Argument #'.$reflParam->getPosition().'($'.$reflParam->getName()
						.') of '.$reflClass->getName().'::__construct');
				}
			} else if ($reflParam->isOptional()) {
				try {
					$args[] = $this->resolve($paramClass->getName());
				} catch (ReflectionException $e) {
					$args[] = null;
				}
			} else {
				$args[] = $this->resolve($paramClass->getName());
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

	/**
	 * {@inheritdoc}
	 */
	public function isBound($abstract)
	{
		if (isset($this->aliases[$abstract])) {
			$abstract = $this->aliases[$abstract];
		}

		return isset($this->instances[$abstract])
			|| isset($this->factories[$abstract]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolving($key, callable $callback)
	{
		$this->resolvingCallbacks[$key][] = $callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolvingAny(callable $callback)
	{
		$this->resolvingAnyCallbacks[] = $callback;
	}
}
