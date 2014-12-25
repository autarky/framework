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

use Autarky\Container\Factory\Definition;
use Autarky\Container\Factory\FactoryInterface;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Default implementation of the container.
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
	 * @var array
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
	 * Internal classes.
	 *
	 * @var array
	 */
	protected $internals = [];

	/**
	 * Whether internal classes should be protected from resolving or not.
	 *
	 * @var boolean
	 */
	protected $protectInternals = true;

	/**
	 * Whether to "autowire" classes or not.
	 *
	 * @var boolean
	 */
	protected $autowire = true;

	/**
	 * Create a new instance of the container.
	 *
	 * On instantiation, the container instance will bind itself onto itself,
	 * and alias the ContainerInterface to the class name.
	 */
	public function __construct()
	{
		$this->instance('Autarky\Container\Container', $this);
		$this->alias('Autarky\Container\Container', 'Autarky\Container\ContainerInterface');
	}

	/**
	 * {@inheritdoc}
	 */
	public function define($class, $factory, array $params = array())
	{
		if ($params) {
			$this->params($class, $params);
		}

		if (!$factory instanceof FactoryInterface) {
			$factory = Definition::getDefaultForCallable($factory);
		}

		return $this->factories[$class] = $factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function invoke($callable, array $params = array())
	{
		if (is_string($callable) && !is_callable($callable)) {
			$callable = [$callable, 'invoke'];
		}

		if (is_string($callable) && strpos($callable, '::') !== false) {
			$callable = explode('::', $callable);
		}

		$class = null;
		$object = null;

		if (is_array($callable)) {
			$class = $callable[0];
			$method = $callable[1];

			if (is_object($class)) {
				$object = $class;
				$class = get_class($object);
			} else {
				$object = $this->resolve($class);
			}

			$reflFunc = new ReflectionMethod($object, $method);

			if ($reflFunc->isStatic()) {
				$object = null;
			}
		} else if (is_callable($callable)) {
			$reflFunc = new ReflectionFunction($callable);
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
	public function resolve($class, array $params = array())
	{
		$alias = null;

		if (array_key_exists($class, $this->aliases)) {
			$alias = $class;
			$class = $this->aliases[$class];
		}

		$this->checkProtected($class, $alias);

		if (array_key_exists($class, $this->instances)) {
			return $this->instances[$class];
		}

		if (array_key_exists($class, $this->params)) {
			$params = array_replace($this->params[$class], $params);
		}

		$previousState = $this->protectInternals;
		$this->protectInternals = false;

		if (!isset($this->factories[$class]) && $this->autowire) {
			$this->factories[$class] = Definition::getDefaultForClass($class);
		}

		if (array_key_exists($class, $this->factories)) {
			$object = $this->callFactory($this->factories[$class], $params);
		} else {
			if ($alias) {
				$class = "$class (via $alias)";
			}
			throw new Exception\ResolvingException("No factory defined for $class");
		}

		$this->protectInternals = $previousState;

		if ($object instanceof ContainerAwareInterface) {
			$object->setContainer($this);
		}

		if ($alias) {
			$this->callResolvingCallbacks($alias, $object);
		}
		$this->callResolvingCallbacks($class, $object);

		if ($this->isShared($class)) {
			$this->instances[$class] = $object;
		}

		return $object;
	}

	/**
	 * Call a factory.
	 *
	 * @param  mixed  $factory
	 * @param  array  $params
	 *
	 * @return object
	 */
	protected function callFactory(Factory\FactoryInterface $factory, array $params = array())
	{
		return $factory->invoke($this, $params);
	}

	/**
	 * Make a new factory definition for a class.
	 *
	 * @param  callable $callable
	 *
	 * @return Definition
	 */
	public function makeFactory($callable)
	{
		return new Definition($callable);
	}

	/**
	 * Get the existing factory for a class. If a factory is not already defined
	 * a default one will be created via reflection.
	 *
	 * @param  string $class  Name of the class
	 * @param  array  $params Optional
	 *
	 * @return FactoryInterface
	 */
	public function getFactory($class, array $params = array())
	{
		if (!isset($this->factories[$class]) && $this->autowire) {
			$this->factories[$class] = Definition::getDefaultForClass($class);
		}

		$factory = $this->factories[$class];

		if ($params) {
			$factory = $factory->getFactory($params);
		}

		return $factory;
	}

	/**
	 * Get an array of arguments to a function, resolving type-hinted arguments
	 * automatically on the way.
	 *
	 * @param  ReflectionFunctionAbstract $func
	 * @param  array                      $params
	 *
	 * @return array
	 *
	 * @throws Exception\UnresolvableArgumentException If any of the arguments are not type-hinted, does not have a default value and is not specified in $params
	 */
	protected function getFunctionArguments(ReflectionFunctionAbstract $func, array $params = array())
	{
		$args = [];

		foreach ($func->getParameters() as $param) {
			$class = $param->getClass();

			if ($class) {
				$args[] = $this->resolveClassArg($class, $param, $params);
			} else {
				$args[] = $this->resolveNonClassArg($param, $params, $func);
			}
		}

		return $args;
	}

	/**
	 * Resolve a class type-hinted argument for a funtion.
	 *
	 * @param  ReflectionClass     $class
	 * @param  ReflectionParameter $param
	 * @param  array               $params
	 *
	 * @return object|null
	 */
	protected function resolveClassArg(ReflectionClass $class, ReflectionParameter $param, array $params)
	{
		$name = '$'.$param->getName();
		$class = $class->getName();

		// loop to prevent code repetition. executes once trying to find the
		// parameter name in the $params array, then once more trying to find
		// the class name (typehint) of the parameter.
		while ($name !== null) {
			if ($params && array_key_exists($name, $params)) {
				$class = $params[$name];
			}

			if ($class instanceof Factory\FactoryInterface) {
				return $class->invoke();
			}

			if (is_object($class)) {
				return $class;
			}

			$name = ($name != $class) ? $class : null;
		}

		try {
			return $this->resolve($class);
		} catch (ReflectionException $exception) {
			if ($param->isOptional()) {
				return null;
			}

			throw $exception;
		}
	}

	/**
	 * Resolve a non-class function argument.
	 *
	 * @param  ReflectionParameter        $param
	 * @param  array                      $params
	 * @param  ReflectionFunctionAbstract $func
	 *
	 * @return mixed
	 */
	protected function resolveNonClassArg(ReflectionParameter $param, array $params, ReflectionFunctionAbstract $func)
	{
		$name = '$'.$param->getName();

		if ($params && array_key_exists($name, $params)) {
			$argument = $params[$name];

			if (is_array($argument) && array_key_exists($argument[0], $this->factories)) {
				$argument = $this->callFactory($argument[0], $argument[1]);
			}

			return $argument;
		}

		if ($param->isDefaultValueAvailable()) {
			return $param->getDefaultValue();
		}

		throw Exception\UnresolvableArgumentException::fromReflectionParam($param, $func);
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolving($classOrClasses, callable $callback)
	{
		foreach ((array) $classOrClasses as $class) {
			$this->resolvingCallbacks[$class][] = $callback;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolvingAny(callable $callback)
	{
		$this->resolvingAnyCallbacks[] = $callback;
	}

	/**
	 * Call resolving callbacks for an object.
	 *
	 * @param  string $key    Container key - usually the class name
	 * @param  object $object
	 *
	 * @return void
	 */
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

	/**
	 * Enable or disable autowiring.
	 *
	 * @param boolean $autowire
	 */
	public function setAutowire($autowire)
	{
		$this->autowire = (bool) $autowire;
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
	public function instance($class, $instance)
	{
		$this->shared[$class] = true;
		$this->instances[$class] = $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function share($classOrClasses)
	{
		foreach ((array) $classOrClasses as $class) {
			$this->shared[$class] = true;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function internal($classOrClasses)
	{
		foreach ((array) $classOrClasses as $class) {
			$this->internals[$class] = true;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function alias($original, $aliasOrAliases)
	{
		foreach ((array) $aliasOrAliases as $alias) {
			$this->aliases[$alias] = $original;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function params($classOrClasses, array $params)
	{
		foreach ((array) $classOrClasses as $class) {
			if (!array_key_exists($class, $this->params)) {
				$this->params[$class] = $params;
			} else {
				$this->params[$class] = array_replace($this->params[$class], $params);
			}
		}
	}

	/**
	 * Determine if a class is shared or not.
	 *
	 * @param  string  $class
	 *
	 * @return boolean
	 */
	protected function isShared($class)
	{
		return array_key_exists($class, $this->shared) && $this->shared[$class];
	}

	/**
	 * Check if a class and its alias (optionally) are protected, and throw an
	 * exception if they are.
	 *
	 * @param  string $class
	 * @param  string|null $alias
	 *
	 * @return void
	 *
	 * @throws Exception\ResolvingInternalException If class or alias is internal
	 */
	protected function checkProtected($class, $alias)
	{
		if (!$this->protectInternals) {
			return;
		}

		if ($alias) {
			if ($this->isProtected($class) || $this->isProtected($alias)) {
				$msg = "Class $class (via alias $alias) or its alias is internal and cannot be resolved.";
				throw new Exception\ResolvingInternalException($msg);
			}
		} else {
			if ($this->isProtected($class)) {
				$msg = "Class $class is internal and cannot be resolved.";
				throw new Exception\ResolvingInternalException($msg);
			}
		}
	}

	/**
	 * Determine if a class is protected or not.
	 *
	 * @param  string  $class
	 *
	 * @return boolean
	 */
	protected function isProtected($class)
	{
		return array_key_exists($class, $this->internals) && $this->internals[$class];
	}
}
