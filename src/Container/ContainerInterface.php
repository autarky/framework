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

/**
 * The container in Autarky is a combination of a service locator and a
 * dependency injector. Whenever dealing with classes that have dependencies,
 * the container should usually be told to resolve an instance of that class
 * rather than instantiating it yourself.
 *
 * @link http://en.wikipedia.org/wiki/Service_locator_pattern
 * @link http://en.wikipedia.org/wiki/Inversion_of_control
 * @link http://martinfowler.com/articles/injection.html
 */
interface ContainerInterface
{
	/**
	 * Define a factory for a given class.
	 *
	 * The factory can be a closure, a string containing the name of a function,
	 * an array of [$object, 'method'] or an array of ['Class', 'method']. If
	 * the latter is used, 'Class' will be resolved out of the container.
	 *
	 * @param  string   $class
	 * @param  callable $factory
	 * @param  array    $params  See ContainerInterface::params()
	 *
	 * @return void
	 */
	public function define($class, $factory, array $params = array());

	/**
	 * Place an already instantiated object into the container. This will make
	 * it available as a shared instance.
	 *
	 * The $class argument should usually be the exact class name of the
	 * instance, except in cases of mocking.
	 *
	 * @param  string $class
	 * @param  object $instance
	 *
	 * @return void
	 */
	public function instance($class, $instance);

	/**
	 * Tell the container that a given class should be a shared instance, i.e.
	 * only constructed once. No matter time how many times that class is
	 * resolved out of the container, it will be the same instance.
	 *
	 * @param  string|array $classOrClasses
	 *
	 * @return void
	 */
	public function share($classOrClasses);

	/**
	 * Define a class or classes as internal.
	 *
	 * Internal classes cannot be resolved directly, but can be resolved as
	 * dependencies to other classes.
	 *
	 * @param  string|array $classOrClasses
	 *
	 * @return void
	 */
	public function internal($classOrClasses);

	/**
	 * Define a set of constructor arguments for a specific class.
	 *
	 * The parameters can be an associative array where the keys are either
	 * class/interface names to map against type-hints of the class' constructor
	 * arguments, or variable names (including the $ prefix).
	 *
	 * @param  string|array $classOrClasses
	 * @param  array        $params
	 *
	 * @return void
	 */
	public function params($classOrClasses, array $params);

	/**
	 * Define an alias.
	 *
	 * Whenever the container is asked to resolve $alias, in any context,
	 * $original should be used instead. Note that it is not possible to have
	 * multiple levels of aliases (e.g. original is aliased to alias1, alias1
	 * is aliased to alias2).
	 *
	 * @param  string       $original
	 * @param  string|array $aliasOrAliases
	 *
	 * @return void
	 */
	public function alias($original, $aliasOrAliases);

	/**
	 * Determine if a class is bound onto the container or not.
	 *
	 * Returns true if a factory is defined, if the class is defined as shared,
	 * or if an instance is set. Aliases are looked up.
	 *
	 * @param  string $class
	 *
	 * @return boolean
	 */
	public function isBound($class);

	/**
	 * Resolve a class from the container. Dependencies of the resolved
	 * object will be resolved recursively.
	 *
	 * If the object resolved is an instance of ContainerAwareInterface, the
	 * container will call setContainer($this) on it.
	 *
	 * @param  string $class
	 * @param  array  $params See ContainerInterface::params()
	 *
	 * @return mixed
	 */
	public function resolve($class, array $params = array());

	/**
	 * Execute a function, closure or class method, resolving type-hinted
	 * arguments as necessary.
	 *
	 * Callable can be anything that passes is_callable() in PHP, including an
	 * array of ['ClassName', 'method'], in which case the class will first be
	 * resolved from the container. Callable can also be some things that don't
	 * pass is_callable(), for example ['InterfaceName', 'method'], but only if
	 * 'InterfaceName' is bound to the container somehow.
	 *
	 * @param  callable $callable
	 * @param  array    $params   See ContainerInterface::params()
	 *
	 * @return mixed
	 */
	public function invoke($callable, array $params = array());

	/**
	 * Register a callback for whenever the given class is resolved.
	 *
	 * This works for both aliases and original classes.
	 *
	 * @param  string|array $classOrClasses
	 * @param  callable     $callback
	 *
	 * @return void
	 */
	public function resolving($classOrClasses, callable $callback);

	/**
	 * Register a callback for whenever anything is resolved.
	 *
	 * @param  callable $callback
	 *
	 * @return void
	 */
	public function resolvingAny(callable $callback);
}
