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
	 * Bind something to the container. Something that is bound will be re-
	 * constructed each time, so there is no singleton. The exception is if
	 * $concrete is an object, in which case the object will be bound onto the
	 * container as a singleton.
	 *
	 * @param  string $abstract
	 * @param  mixed  $concrete
	 *
	 * @return void
	 */
	public function bind($abstract, $concrete = null);

	/**
	 * Bind something to the container and share it, effectively making it a
	 * shared/singleton object.
	 *
	 * @param  string $abstract
	 * @param  mixed  $concrete
	 *
	 * @return void
	 */
	public function share($abstract, $concrete = null);

	/**
	 * Define an alias.
	 *
	 * @param  string $alias
	 * @param  string $target The abstract key the alias points towards.
	 *
	 * @return void
	 */
	public function alias($alias, $target);

	/**
	 * Determine if a class/key is bound onto the container or not.
	 *
	 * @param  string  $abstract
	 *
	 * @return boolean
	 */
	public function isBound($abstract);

	/**
	 * Resolve an object from the IoC container. Dependencies of the resolved
	 * object should also be resolved recursively if possible.
	 *
	 * If the object resolved is an instance of ContainerAwareInterface, the
	 * container should call setContainer($this) on it.
	 *
	 * @param  string $abstract
	 *
	 * @return mixed
	 */
	public function resolve($abstract);

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
