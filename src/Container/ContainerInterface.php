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

interface ContainerInterface
{
	/**
	 * Bind something to the container.
	 *
	 * $abstract must be a string. It should be the key for which the concrete
	 * is stored in the IoC container. Usually this should be the class name or
	 * the interface/abstract class which role it fulfills, but it can also be
	 * a more generic key like "session" or "db".
	 *
	 * $concrete can either be an object, a string or a closure. If an object
	 * is provided, then that object is stored as a singleton service which is
	 * returned by resolve() each time.
	 *
	 * If a string is provided, then the IoC container will try to resolve by
	 * this key when $abstract is requested via resolve(). If a closure is
	 * provided, then the return value of this closure will be the return value
	 * of resolve().
	 *
	 * If $concrete is left out, it just means you want to tell the IoC
	 * container it should know about $abstract for events or whatever else.
	 *
	 * @param  string $abstract The IoC key.
	 * @param  mixed  $concrete What should be resolved.
	 *
	 * @return void
	 */
	public function bind($abstract, $concrete = null);

	/**
	 * Bind something to the container and share it.
	 *
	 * This works like bind, except it binds it as a shared/singleton object.
	 *
	 * @param  string $abstract
	 * @param  mixed  $concrete
	 *
	 * @return void
	 */
	public function share($abstract, $concrete = null);

	/**
	 * Resolve an object from the IoC container.
	 *
	 * @param  string $abstract
	 *
	 * @return mixed
	 */
	public function resolve($abstract);
}
