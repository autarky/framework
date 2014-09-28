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
use ReflectionFunction;
use ReflectionFunctionAbstract;

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
	 * @var array
	 */
	protected $factories = [];

	/**
	 * Classes that should be shared instances.
	 *
	 * @var string
	 */
	protected $shared = [];

	/**
	 * Aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * Parameter specifications.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Resolving callbacks.
	 *
	 * @var array
	 */
	protected $resolvingCallbacks = [];

	/**
	 * More resolving callbacks.
	 *
	 * @var array
	 */
	protected $resolvingAnyCallbacks = [];

	/**
	 * {@inheritdoc}
	 */
	public function define($class, $factory)
	{
		$isArray = is_array($factory);
		$isCallable = is_callable($factory);

		if ($isArray && !$isCallable) {
			$factory = function($container) use($factory) {
				return $container->execute($factory);
			};
		}

		if (!$isCallable && !$isArray) {
			$type = is_object($factory) ? get_class($factory) : gettype($factory);
			throw new \InvalidArgumentException("Factory must be a callable or array, $type given");
		}

		$this->factories[$class] = $factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function instance($class, $instance)
	{
		$this->shared[$class] = true;
		$this->instances[$class] = $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function share($class)
	{
		$this->shared[$class] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function alias($original, $alias)
	{
		$this->aliases[$alias] = $original;
	}

	/**
	 * {@inheritdoc}
	 */
	public function params($class, array $params)
	{
		if (!array_key_exists($class, $this->params)) {
			$this->params[$class] = $params;
		} else {
			$this->params[$class] = array_replace($this->params[$class], $params);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute($callable, array $params = array())
	{
		if (is_string($callable) && strpos($callable, '::') !== false) {
			$callable = explode('::', $callable);
		}

		if (is_array($callable)) {
			$class = $callable[0];
			$method = $callable[1];
			$object = $this->resolve($class);
			$reflFunc = new ReflectionMethod($object, $method);
		} else if (is_string($callable) || is_callable($callable)) {
			$reflFunc = new ReflectionFunction($callable);
			$class = null;
		} else {
			$type = is_object($callable) ? get_class($callable) : gettype($callable);
			throw new \InvalidArgumentException("Callable must be a callable or array, $type given");
		}

		if ($class && array_key_exists($class, $this->params)) {
			$params = array_replace($this->params[$class], $params);
		}

		$args = $this->getFunctionArguments($reflFunc, $params);

		if ($class) {
			return $reflFunc->invokeArgs($object, $args);
		}

		return $reflFunc->invokeArgs($args);
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolve($class)
	{
		if (array_key_exists($class, $this->aliases)) {
			$class = $this->aliases[$class];
		}

		if (array_key_exists($class, $this->instances)) {
			return $this->instances[$class];
		}

		if (array_key_exists($class, $this->factories)) {
			$object = call_user_func($this->factories[$class], $this);
		} else {
			$object = $this->build($class);
		}

		if ($object instanceof ContainerAwareInterface) {
			$object->setContainer($this);
		}

		$this->callResolvingCallbacks($class, $object);

		if ($this->isShared($class)) {
			$this->instances[$class] = $object;
		}

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

	protected function isShared($class)
	{
		return array_key_exists($class, $this->shared) && $this->shared[$class];
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

		$params = array_key_exists($class, $this->params) ? $this->params[$class] : null;

		$args = $this->getFunctionArguments($reflClass->getMethod('__construct'), $params);

		return $reflClass->newInstanceArgs($args);
	}

	protected function getFunctionArguments(ReflectionFunctionAbstract $reflFunc, array $params = null)
	{
		$args = [];

		foreach ($reflFunc->getParameters() as $param) {
			$name = $param->getName();
			$paramClass = $param->getClass();

			if ($paramClass) {
				$paramClass = $paramClass->getName();

				if ($params && array_key_exists("\$$name", $params)) {
					$paramClass = $params["\$$name"];
				}

				if (is_object($paramClass)) {
					$args[] = $paramClass;
					continue;
				}

				if ($params && array_key_exists($paramClass, $params)) {
					$paramClass = $params[$paramClass];
				}

				if (is_object($paramClass)) {
					$args[] = $paramClass;
					continue;
				}

				try {
					$args[] = $this->resolve($paramClass);
				} catch (ReflectionException $exception) {
					if ($param->isOptional()) {
						$args[] = null;
					} else {
						throw $exception;
					}
				}
			} else {
				if ($params && array_key_exists("\$$name", $params)) {
					$args[] = $params["\$$name"];
				} else if ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					if ($reflFunc instanceof ReflectionMethod) {
						$funcName = $reflFunc->getDeclaringClass()->getName()
							.'::'.$reflFunc->getName();
					} else {
						$funcName = $reflFunc->getName();
					}
					throw new UnresolvableDependencyException('Unresolvable dependency: '
						.'Argument #'.$param->getPosition().' ($'.$name.') of '.$funcName);
				}
			}
		}

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isBound($class)
	{
		if (array_key_exists($class, $this->aliases)) {
			$class = $this->aliases[$class];
		}

		return array_key_exists($class, $this->instances)
			|| array_key_exists($class, $this->factories)
			|| array_key_exists($class, $this->shared);
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
