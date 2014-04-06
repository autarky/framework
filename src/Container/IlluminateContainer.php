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
use Illuminate\Container\Container;

class IlluminateContainer implements ContainerInterface
{
	public function __construct(Container $container = null)
	{
		$this->container = $container ?: new Container;
	}

	public function bind($abstract, $concrete = null)
	{
		return $this->container->bind($abstract, $concrete);
	}

	public function share($abstract, $concrete = null)
	{
		if ($concrete === null || is_string($concrete)) {
			return $this->container->singleton($abstract, $concrete);
		} elseif ($concrete instanceof Closure) {
			return $this->container->bindShared($abstract, $concrete);
		} elseif (is_object($concrete)) {
			return $this->container->instance($abstract, $concrete);
		} else {
			throw new \InvalidArgumentException('$concrete must be null, string,'
				.' closure or object, '.gettype($concrete).' given');
		}
	}

	public function alias($alias, $target)
	{
		return $this->container->alias($target, $alias);
	}

	public function resolve($abstract)
	{
		return $this->container->make($abstract);
	}
}
