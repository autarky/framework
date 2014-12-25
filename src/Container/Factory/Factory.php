<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Factory;

use Autarky\Container\ContainerInterface;
use Autarky\Container\Exception\UnresolvableArgumentException;

/**
 * A factory.
 */
class Factory implements FactoryInterface
{
	/**
	 * The factory's definition.
	 *
	 * @var Definition
	 */
	protected $definition;

	/**
	 * The factory's default parameters.
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * Constructor.
	 *
	 * @param Definition $definition
	 * @param array      $params
	 */
	public function __construct(Definition $definition, array $params = array())
	{
		$this->definition = $definition;
		$this->params = $params;
	}

	/**
	 * Get a new instance of the factory.
	 *
	 * @param  array  $params
	 *
	 * @return static
	 */
	public function getFactory(array $params = array())
	{
		$factory = clone $this;
		$factory->params = $params;
		return $factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function invoke(ContainerInterface $container, array $params = array())
	{
		$params = array_replace($this->params, $params);
		$callable = $this->definition->getCallable();

		if (is_array($callable) && is_string($callable[0])) {
			$callable[0] = $container->resolve($callable[0]);
		}

		$args = [];

		foreach ($this->definition->getArguments() as $arg) {
			if ($arg->isClass()) {
				$resolvedArg = $this->resolveClassArg($container, $arg, $params);
			} else {
				$resolvedArg = $this->resolveScalarArg($container, $arg, $params);
			}

			if ($resolvedArg instanceof FactoryInterface) {
				$resolvedArg = $resolvedArg->invoke($container);
			}

			$args[$arg->getPosition()] = $resolvedArg;
		}

		return call_user_func_array($callable, $args);
	}

	protected function resolveClassArg(ContainerInterface $container, ClassArgument $arg, array $params)
	{
		$name = $arg->getName();
		$class = $arg->getClass();

		// loop to prevent code repetition. executes once trying to find the
		// parameter name in the $params array, then once more trying to find
		// the class name (typehint) of the parameter.
		while ($name !== null) {
			if ($params && array_key_exists($name, $params)) {
				$class = $params[$name];
			}

			if (is_object($class)) {
				return $class;
			}

			$name = ($name != $class) ? $class : null;
		}

		try {
			return $container->resolve($class);
		} catch (\ReflectionException $exception) {
			if (!$arg->isRequired()) {
				return null;
			}

			throw $exception;
		}
	}

	protected function resolveScalarArg(ContainerInterface $container, ScalarArgument $arg, array $params)
	{
		$name = $arg->getName();

		if ($params && array_key_exists($name, $params)) {
			return $params[$name];
		}

		if (!$arg->isRequired()) {
			return $arg->getDefault();
		}

		throw new UnresolvableArgumentException("Argument $name is required and has no value");
	}
}
