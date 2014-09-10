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
use ReflectionMethod;

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
		if (is_callable($concrete)) {
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

		if (is_callable($concrete)) {
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
		if (array_key_exists($abstract, $this->aliases)) {
			$abstract = $this->aliases[$abstract];
		}

		if (array_key_exists($abstract, $this->instances)) {
			return $this->instances[$abstract];
		}

		if (array_key_exists($abstract, $this->factories)) {
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
			call_user_func($callback, $object, $this);
		}

		if (array_key_exists($key, $this->resolvingCallbacks)) {
			foreach ($this->resolvingCallbacks[$key] as $callback) {
				call_user_func($callback, $object, $this);
			}
		}
	}

	protected function build($class)
	{
		if (!class_exists($class)) {
			throw new NotInstantiableException("Class $class does not exist");
		}

		$reflClass = new ReflectionClass($class);

		if (!$reflClass->isInstantiable()) {
			throw new NotInstantiableException("Class $class is not instantiable");
		}

		if (!$reflClass->hasMethod('__construct')) {
			return $reflClass->newInstance();
		}

		$args = $this->getMethodArguments($reflClass->getMethod('__construct'));

		return $reflClass->newInstanceArgs($args);
	}

	protected function getMethodArguments(ReflectionMethod $method)
	{
		$args = [];

		foreach ($method->getParameters() as $param) {
			// the type-hint of the parameter if typehinted against a class.
			// otherwise null/false
			$class = $param->getClass();

			if ($class) {
				try {
					$args[] = $this->resolve($class->getName());
				} catch (ReflectionException $exception) {
					if ($param->isOptional()) {
						$args[] = null;
					} else {
						throw $exception;
					}
				}
			} else {
				if ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					throw new UnresolvableDependencyException('Unresolvable dependency: '
						.'Argument #'.$param->getPosition().' ($'.$param->getName()
						.') of '.$method->getName());
				}
			}
		}

		return $args;
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
		if (array_key_exists($abstract, $this->aliases)) {
			$abstract = $this->aliases[$abstract];
		}

		return array_key_exists($abstract, $this->instances)
			|| array_key_exists($abstract, $this->factories);
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
