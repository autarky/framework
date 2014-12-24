<?php
namespace Autarky\Container\Factory;

use Autarky\Container\ContainerInterface;
use Autarky\Container\Exception\NotInstantiableException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class Definition implements FactoryInterface
{
	protected $callable;
	protected $argumentPositions = [];
	protected $argumentNames = [];
	protected $argumentClasses = [];
	protected $argumentPosition = 0;

	public function __construct($callable)
	{
		$this->callable = $callable;
	}

	public static function getDefaultForClass($class)
	{
		$reflectionClass = new ReflectionClass($class);
		if (!$reflectionClass->isInstantiable()) {
			throw new NotInstantiableException("Class $class is not instantiable");
		}
		$factory = new static([$reflectionClass, 'newInstance']);

		if ($reflectionClass->hasMethod('__construct')) {
			$reflectionFunction = $reflectionClass->getMethod('__construct');

			foreach ($reflectionFunction->getParameters() as $arg) {
				if ($argClass = $arg->getClass()) {
					$factory->addClassArgument($arg->getName(), $argClass->getName(), !$arg->isOptional());
				} else {
					$factory->addScalarArgument($arg->getName(), null, !$arg->isOptional(), ($arg->isOptional() ? $arg->getDefaultValue() : null));
				}
			}
		}

		return $factory;
	}

	public static function getDefaultForCallable($callable)
	{
		$factory = new static($callable);
		if (is_array($callable)) {
			$reflectionFunction = new ReflectionMethod($callable[0], $callable[1]);
		} else {
			$reflectionFunction = new ReflectionFunction($callable);
		}

		foreach ($reflectionFunction->getParameters() as $arg) {
			if ($argClass = $arg->getClass()) {
				$factory->addClassArgument($arg->getName(), $argClass->getName(), !$arg->isOptional());
			} else {
				$factory->addScalarArgument($arg->getName(), null, !$arg->isOptional(), ($arg->isOptional() ? $arg->getDefaultValue() : null));
			}
		}

		return $factory;
	}

	public function getCallable()
	{
		return $this->callable;
	}

	public function getArguments()
	{
		return $this->argumentPositions;
	}

	public function addScalarArgument($name, $type, $required = true, $default = null)
	{
		return $this->addArgument(new ScalarArgument($this->argumentPosition++, $name, $type, $required, $default));
	}

	public function addClassArgument($name, $class, $required = true)
	{
		return $this->addArgument(new ClassArgument($this->argumentPosition++, $name, $class, $required));
	}

	public function addArgument(ArgumentInterface $argument)
	{
		$this->argumentPositions[$argument->getPosition()] = $argument;
		$this->argumentNames[$argument->getName()] = $argument;
		if ($argument->isClass()) {
			$this->argumentClasses[$argument->getClass()] = $argument;
		}
		return $argument;
	}

	public function getFactory(array $params = array())
	{
		return new Factory($this, $params);
	}

	public function invoke(ContainerInterface $container, array $params = array())
	{
		return $this->getFactory($params)
			->invoke($container);
	}
}
