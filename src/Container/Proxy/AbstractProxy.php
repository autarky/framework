<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Proxy;

use Autarky\Container\ContainerInterface;

abstract class AbstractProxy
{
	protected static $instances = [];
	protected static $container;

	public static function setProxyContainer(ContainerInterface $container = null)
	{
		static::$instances = [];
		static::$container = $container;
	}

	public static function setProxyInstance($instance = null)
	{
		static::$instances[static::getProxyIocKey()] = $instance;
	}

	protected static function resolveProxyInstance()
	{
		return static::$container->resolve(static::getProxyIocKey());
	}

	public static function __callStatic($method, array $args)
	{
		$key = static::getProxyIocKey();

		if (!array_key_exists($key, static::$instances)) {
			static::$instances[$key] = static::resolveProxyInstance();
		}

		return call_user_func_array([static::$instances[$key], $method], $args);
	}

	protected static function getProxyIocKey()
	{
		// abstract static methods are not allowed, so do this instead
		throw new \RuntimeException('Method getProxyIocKey must be implemented.');
	}
}
