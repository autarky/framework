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
 * A container in Autarky is a class that contains information about how to
 * resolve different objects.
 *
 * The container has two jobs - it needs to receive information (usually from
 * service providers) on how it should resolve different keys/classes/interfaces
 * and, of course, resolve these when asked to.
 *
 * A few method arguments repeat themselves in this interface's methods.
 *
 * $abstract is the key that the container should store the metainformation
 * under. Under the hood the container keeps a hashmap of abstract => concrete
 * in some fashion. $abstract can be a plain string or a string that represeents
 * a class or an interface.
 *
 * $concrete tells the container how $abstract should be resolved when it is
 * requested. Sometimes $concrete will be left as null - in this case, $abstract
 * should fill $concrete's role. Common use cases for this would be binding a
 * singleton class onto the container but not constructing it until it is
 * requested to be resolved.
 *
 * The container should to the best of its abilities try and recursively resolve
 * all classes' dependencies automatically.
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
	 * @param  string $class
	 * @param  mixed  $factory
	 *
	 * @return void
	 */
	public function define($class, $factory);

	/**
	 * Place an already instantiated object into the container.
	 *
	 * @param  string $class
	 * @param  object $instance
	 *
	 * @return void
	 */
	public function instance($class, $instance);

	/**
	 * Tell the container that a given class should be a shared instance, i.e.
	 * only constructed once.
	 *
	 * @param  string $class
	 *
	 * @return void
	 */
	public function share($class);

	/**
	 * Define a set of constructor arguments for a specific class.
	 *
	 * @param  string $class
	 * @param  array  $params
	 *
	 * @return void
	 */
	public function params($class, array $params);

	/**
	 * Define an alias.
	 *
	 * @param  string $original
	 * @param  string $alias
	 *
	 * @return void
	 */
	public function alias($original, $alias);

	/**
	 * Determine if a class is bound onto the container or not.
	 *
	 * @param  string  $class
	 *
	 * @return boolean
	 */
	public function isBound($class);

	/**
	 * Resolve a class from the container. Dependencies of the resolved
	 * object should also be resolved recursively if possible.
	 *
	 * If the object resolved is an instance of ContainerAwareInterface, the
	 * container should call setContainer($this) on it.
	 *
	 * @param  string $class
	 *
	 * @return mixed
	 */
	public function resolve($class);

	/**
	 * Execute a function, closure or class method, resolving type-hinted
	 * arguments as necessary.
	 *
	 * @param  string|array $callable
	 * @param  array        $params
	 *
	 * @return mixed
	 */
	public function invoke($callable, array $params = array());

	/**
	 * Register a callback for whenever the given key is resolved.
	 *
	 * @param  string   $key
	 * @param  callable $callback
	 *
	 * @return void
	 */
	public function resolving($key, callable $callback);

	/**
	 * Register a callback for whenever anything is resolved.
	 *
	 * @param  callable $callback
	 *
	 * @return void
	 */
	public function resolvingAny(callable $callback);
}
